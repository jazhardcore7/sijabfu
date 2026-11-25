<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * Alur lengkap lewat Admin Satuan Kerja Provinsi (FR-SATKER-7 s.d. FR-SATKER-12),
 * ditambah regresi Pegawai Pusat yang tetap mengajukan sendiri (FR-SATKER-7).
 */
class SatkerFullFlowTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_full_flow_submitted_by_admin(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->kabkota('32', '73')->create(['nip' => '199202022020022002']);
        $timJf = User::factory()->timJf()->create();

        // 1. Admin mengirim usulan untuk pegawai
        $this->actingAs($admin)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $pegawai->id, 'aksi' => 'kirim', 'berkas' => $this->allBerkas()]))
            ->assertRedirect(route('satker.pengajuan.index'));
        $usulan = FormUsulan::sole();

        // 2. Tim JF memutuskan TMS
        $skp = $usulan->berkas()->where('jenis_persyaratan', 'skp')->first();
        $this->actingAs($timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'periksa', 'pemeriksaan' => [$skp->id => ['hasil' => 'perlu_perbaikan', 'catatan' => 'SKP buram']]]);
        $this->actingAs($timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'tms'])->assertRedirect();
        $this->assertSame('perbaikan', $usulan->fresh()->status_key);

        // 3. Pegawai tidak dapat memperbaiki; Admin memperbaiki dan mengirim ulang
        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan())->assertForbidden();
        $this->actingAs($admin)->post("/satker/pengajuan/{$usulan->id}", $this->dataUsulan(['berkas' => ['skp' => $this->pdf('skp-baru.pdf')]]))->assertRedirect();
        $this->actingAs($admin)->post("/satker/pengajuan/{$usulan->id}/kirim-ulang")->assertRedirect();
        $this->assertSame('verifikasi', $usulan->fresh()->status_key);

        // 4-6. MS, surat, sertifikat
        $this->actingAs($timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'ms'])->assertRedirect();
        $this->actingAs($timJf)->post("/monitoring/{$usulan->id}/status", ['aksi' => 'tetapkan', 'surat' => $this->pdf('surat.pdf')])->assertRedirect();
        $this->actingAs($pegawai)->get("/files/surat/{$usulan->id}")->assertOk()->assertDownload('surat-usulan-199202022020022002.pdf');
        $this->actingAs($admin)->get("/files/surat/{$usulan->id}")->assertOk();
        $this->actingAs($timJf)->post("/monitoring/{$usulan->id}/status", ['aksi' => 'selesaikan', 'sertifikat' => $this->pdf('sertifikat.pdf')])->assertRedirect();
        $this->assertSame('selesai', $usulan->fresh()->status_key);
        $this->actingAs($pegawai)->get("/files/sertifikat/{$usulan->id}")->assertOk();
        $this->actingAs($pegawai)->get('/pelacakan-status')->assertOk()->assertSee('data-action="download-sertifikat"', false);

        $this->assertSame(
            [[null, 'verifikasi', $admin->id], ['verifikasi', 'perbaikan', $timJf->id], ['perbaikan', 'verifikasi', $admin->id], ['verifikasi', 'disetujui', $timJf->id], ['disetujui', 'ditetapkan', $timJf->id], ['ditetapkan', 'selesai', $timJf->id]],
            $usulan->statusLogs()->get()->map(fn ($l) => [$l->from_status, $l->to_status, (int) $l->actor_id])->all()
        );
    }

    public function test_pegawai_pusat_still_submits_and_fixes_own_usulan(): void
    {
        Storage::fake('local');
        $pegawai = User::factory()->pegawai()->pusat()->create();
        $timJf = User::factory()->timJf()->create();

        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan(['aksi' => 'kirim', 'berkas' => $this->allBerkas()]))->assertRedirect(route('pelacakan-status'));
        $usulan = FormUsulan::sole();
        $this->assertSame([$pegawai->id, $pegawai->id], [(int) $usulan->user_id, (int) $usulan->submitted_by]);

        $this->actingAs($timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'tms', 'catatan' => 'Lengkapi SKP'])->assertRedirect();
        $this->actingAs($pegawai)->post('/pengajuan/kirim-ulang')->assertRedirect(route('pelacakan-status'));
        $this->assertSame('verifikasi', $usulan->fresh()->status_key);
    }
}
