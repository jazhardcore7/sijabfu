<?php

namespace Tests\Feature\Phase3;

use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanBerkas;
use App\Models\UsulanStatusLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pengajuan Usulan (PRD Revisi 5.3): FR-PU-2 s.d. FR-PU-6.
 * FR-PU-1 (Sinkron SIMPEG/SIASN) ditunda ke Fase 8.
 */
class PengajuanTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->pegawai = User::factory()->pegawai()->create([
            'name' => 'Pegawai Asli',
            'nip' => '199001012020011001',
            'email' => 'asli@bps.go.id',
        ]);
    }

    public function test_page_shows_entri_data_and_upload_slots(): void
    {
        $response = $this->actingAs($this->pegawai)->get('/pengajuan')->assertOk();

        $response->assertSee('Entri Data');
        $response->assertSee('Upload Berkas Persyaratan');
        $response->assertSee('Kirim Usulan');
        foreach (config('sijabfu.berkas_persyaratan') as $key => $label) {
            $response->assertSee('data-slot="'.$key.'"', false);
            $response->assertSee($label);
        }
    }

    // FR-PU-2
    public function test_identity_is_taken_from_logged_in_user_and_posted_values_are_ignored(): void
    {
        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan([
            'nip' => '999999999999999999',
            'nama_lengkap' => 'Nama Palsu',
            'email' => 'palsu@example.test',
        ]))->assertRedirect(route('pengajuan'));

        $usulan = FormUsulan::sole();
        $this->assertSame('199001012020011001', $usulan->nip);
        $this->assertSame('Pegawai Asli', $usulan->nama_lengkap);
        $this->assertSame('asli@bps.go.id', $usulan->email);
        $this->assertSame('Statistisi', $usulan->jabatan_fungsional);
        $this->assertSame('BPS Provinsi Uji', $usulan->satuan_kerja);
        $this->assertNull($usulan->status_key, 'simpan menghasilkan draf');
    }

    // FR-PU-3
    public function test_non_pdf_file_is_rejected(): void
    {
        $this->actingAs($this->pegawai)->from('/pengajuan')->post('/pengajuan', $this->dataUsulan([
            'berkas' => [
                'skp' => UploadedFile::fake()->create('skp.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                'pas_foto' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg'),
            ],
        ]))->assertSessionHasErrors(['berkas.skp', 'berkas.pas_foto']);

        $this->assertSame(0, UsulanBerkas::count());
    }

    // FR-PU-3
    public function test_file_over_5_mb_is_rejected(): void
    {
        $this->actingAs($this->pegawai)->from('/pengajuan')->post('/pengajuan', $this->dataUsulan([
            'berkas' => ['skp' => $this->pdf('skp.pdf', 5121)],
        ]))->assertSessionHasErrors(['berkas.skp' => 'Ukuran berkas maksimal 5 MB.']);

        $this->assertSame(0, UsulanBerkas::count());
    }

    // FR-PU-3
    public function test_file_is_stored_privately_with_random_name(): void
    {
        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan([
            'berkas' => ['skp' => $this->pdf('SKP Saya.pdf')],
        ]))->assertRedirect(route('pengajuan'));

        $berkas = UsulanBerkas::sole();
        $this->assertSame('SKP Saya.pdf', $berkas->original_name);
        $this->assertStringStartsWith('usulan/'.$berkas->form_usulan_id.'/', $berkas->file_path);
        $this->assertStringNotContainsString('SKP Saya', $berkas->file_path);
        Storage::disk('local')->assertExists($berkas->file_path);
        $this->assertSame([], Storage::disk('public')->allFiles(), 'tidak ada file di disk publik');
        $this->assertSame(storage_path('app/private'), config('filesystems.disks.local.root'));
    }

    // FR-PU-4
    public function test_kirim_is_refused_when_a_slot_is_empty(): void
    {
        $tanpaDupak = array_diff(array_keys(config('sijabfu.berkas_persyaratan')), ['dupak']);

        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan([
            'aksi' => 'kirim',
            'berkas' => $this->allBerkas($tanpaDupak),
        ]))->assertRedirect(route('pengajuan'))
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, 'DUPAK'));

        $usulan = FormUsulan::sole();
        $this->assertNull($usulan->status_key, 'masih draf');
        $this->assertSame(7, $usulan->berkas()->count(), 'berkas yang diunggah tetap tersimpan');
        $this->assertSame(0, UsulanStatusLog::count());
    }

    // FR-PU-4
    public function test_kirim_after_uploading_in_steps_sets_verifikasi_and_logs(): void
    {
        $keys = array_keys(config('sijabfu.berkas_persyaratan'));

        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan([
            'berkas' => $this->allBerkas(array_slice($keys, 0, 4)),
        ]));
        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan([
            'aksi' => 'kirim',
            'berkas' => $this->allBerkas(array_slice($keys, 4)),
        ]))->assertRedirect(route('pelacakan-status'));

        $usulan = FormUsulan::sole();
        $this->assertSame('verifikasi', $usulan->status_key);
        $this->assertSame(8, $usulan->berkas()->count());
        $this->assertSame(1, UsulanStatusLog::where('to_status', 'verifikasi')->count());
    }

    // FR-PU-5
    public function test_editing_is_blocked_outside_draft_and_perbaikan(): void
    {
        foreach (['verifikasi', 'disetujui', 'ditetapkan'] as $status) {
            $pegawai = User::factory()->pegawai()->create();
            $usulan = $this->usulanAt($pegawai, $status);
            $oldPath = $usulan->berkas()->where('jenis_persyaratan', 'skp')->value('file_path');

            $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan([
                'nomor_surat' => 'DIUBAH',
                'berkas' => ['skp' => $this->pdf()],
            ]))->assertRedirect(route('pengajuan'))->assertSessionHas('error');

            $usulan->refresh();
            $this->assertSame('001/TEST', $usulan->nomor_surat, "status {$status}");
            $this->assertSame($oldPath, $usulan->berkas()->where('jenis_persyaratan', 'skp')->value('file_path'));

            $this->actingAs($pegawai)->get('/pengajuan')
                ->assertSee('data-section="usulan-diproses"', false)
                ->assertDontSee('id="formPengajuan"', false);
        }
    }

    // FR-PU-5
    public function test_perbaikan_allows_edit_replace_and_kirim_ulang(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'perbaikan', ['catatan' => 'Ganti SKP']);
        $skp = $usulan->berkas()->where('jenis_persyaratan', 'skp')->first();
        $skp->update(['pemeriksaan' => UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN]);
        $oldPath = $skp->file_path;

        $page = $this->actingAs($this->pegawai)->get('/pengajuan')->assertOk();
        $page->assertSee('data-section="perbaikan-banner"', false);
        $page->assertSee('data-action="kirim-ulang"', false);
        $page->assertDontSee('data-action="kirim-usulan"', false);

        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan([
            'nomor_surat' => '002/PERBAIKAN',
            'berkas' => ['skp' => $this->pdf('skp-baru.pdf')],
        ]))->assertRedirect(route('pengajuan'));

        $skp->refresh();
        $this->assertSame('002/PERBAIKAN', $usulan->fresh()->nomor_surat);
        $this->assertSame('skp-baru.pdf', $skp->original_name);
        $this->assertNull($skp->pemeriksaan, 'tanda perbaikan direset saat berkas diganti');
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($skp->file_path);
        $this->assertSame('perbaikan', $usulan->fresh()->status_key, 'simpan tidak mengubah status');

        $this->actingAs($this->pegawai)->post('/pengajuan/kirim-ulang')
            ->assertRedirect(route('pelacakan-status'));

        $this->assertSame('verifikasi', $usulan->fresh()->status_key);
        $this->assertSame(1, UsulanStatusLog::where('from_status', 'perbaikan')->where('to_status', 'verifikasi')->count());
    }

    // FR-PU-5
    public function test_kirim_ulang_outside_perbaikan_is_refused(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'verifikasi');

        $this->actingAs($this->pegawai)->post('/pengajuan/kirim-ulang')
            ->assertRedirect(route('pengajuan'))->assertSessionHas('error');

        $this->assertSame('verifikasi', $usulan->fresh()->status_key);
        $this->assertSame(0, UsulanStatusLog::count());
    }

    // FR-PU-6
    public function test_new_usulan_is_only_allowed_after_previous_is_selesai(): void
    {
        $this->usulanAt($this->pegawai, 'disetujui');

        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan());
        $this->assertSame(1, FormUsulan::count(), 'tidak ada usulan kedua selama usulan aktif');

        FormUsulan::query()->update(['status_key' => 'selesai']);

        $this->actingAs($this->pegawai)->get('/pengajuan')
            ->assertSee('data-section="usulan-selesai"', false)
            ->assertSee('id="formPengajuan"', false);

        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan(['nomor_surat' => 'BARU']))
            ->assertRedirect(route('pengajuan'));

        $this->assertSame(2, FormUsulan::count());
        $this->assertNull(FormUsulan::orderByDesc('id')->first()->status_key);
    }

    public function test_draft_is_hidden_from_staff_lists(): void
    {
        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan(['nomor_surat' => 'DRAF-RAHASIA']));
        $timJf = User::factory()->timJf()->create();

        $this->actingAs($timJf)->get('/dashboard')->assertOk()->assertDontSee('DRAF-RAHASIA');
        $this->actingAs($timJf)->get('/monitoring')->assertOk()->assertDontSee('DRAF-RAHASIA');
    }

    public function test_upload_is_throttled(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan());
        }

        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan())->assertStatus(429);
    }
}
