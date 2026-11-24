<?php

namespace Tests\Feature\Phase4;

use App\Models\FormUsulan;
use App\Models\UsulanStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * Monitoring Progress UKOM (PRD Revisi 5.6): FR-MON-1 s.d. FR-MON-5.
 */
class MonitoringTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $timJf;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->timJf = User::factory()->timJf()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
    }

    private function at(string $status, array $extra = [], ?User $owner = null): FormUsulan
    {
        return $this->usulanAt($owner ?? User::factory()->pegawai()->create(), $status, $extra);
    }

    // FR-MON-1
    public function test_counts_equal_database_and_filters_work(): void
    {
        $expected = ['verifikasi' => 3, 'perbaikan' => 1, 'disetujui' => 2, 'ditetapkan' => 1, 'selesai' => 4];
        foreach ($expected as $status => $n) {
            for ($i = 0; $i < $n; $i++) {
                $this->at($status);
            }
        }
        $this->at('verifikasi', [], User::factory()->pegawai()->create(['name' => 'Siti Cari', 'nip' => '777']));
        $this->usulanAt(User::factory()->pegawai()->create(), null); // draf tidak dihitung
        $expected['verifikasi']++;

        $html = $this->actingAs($this->timJf)->get('/monitoring')->assertOk()->getContent();

        foreach ($expected as $status => $n) {
            $this->assertSame(FormUsulan::where('status_key', $status)->count(), $n);
            $this->assertMatchesRegularExpression('/data-stat="' . $status . '".*?data-count="' . $n . '"/s', $html, $status);
        }
        $this->assertMatchesRegularExpression('/data-stat="total".*?<div class="stat-count">' . array_sum($expected) . '</s', $html);

        $filtered = $this->actingAs($this->timJf)->get('/monitoring?status=selesai')->getContent();
        $this->assertSame(4, substr_count($filtered, 'class="usulan-item-card"'));

        $this->actingAs($this->timJf)->get('/monitoring?q=777')->assertSee('Siti Cari');
        $this->assertSame(1, substr_count($this->actingAs($this->timJf)->get('/monitoring?q=Siti%20Cari')->getContent(), 'class="usulan-item-card"'));
    }

    // FR-MON-1
    public function test_pagination_is_15_per_page(): void
    {
        for ($i = 0; $i < 17; $i++) {
            $this->at('verifikasi');
        }

        $this->assertSame(15, substr_count($this->actingAs($this->timJf)->get('/monitoring')->getContent(), 'class="usulan-item-card"'));
        $this->assertSame(2, substr_count($this->actingAs($this->timJf)->get('/monitoring?page=2')->getContent(), 'class="usulan-item-card"'));
    }

    // FR-MON-4 + FR-MON-3
    public function test_tetapkan_with_surat_pdf_sets_ditetapkan_and_logs(): void
    {
        $usulan = $this->at('disetujui');

        $this->actingAs($this->timJf)->post("/monitoring/{$usulan->id}/status", [
            'aksi' => 'tetapkan',
            'surat' => $this->pdf('surat.pdf'),
        ])->assertRedirect(route('monitoring.index'));

        $fresh = $usulan->fresh();
        $this->assertSame('ditetapkan', $fresh->status_key);
        Storage::disk('local')->assertExists($fresh->surat_usulan_path);
        $this->assertStringStartsWith('usulan/' . $usulan->id . '/', $fresh->surat_usulan_path);
        $this->assertSame(1, UsulanStatusLog::where('form_usulan_id', $usulan->id)->where('to_status', 'ditetapkan')->count());
    }

    // FR-MON-4 + FR-MON-3
    public function test_selesaikan_with_sertifikat_pdf_sets_selesai(): void
    {
        $usulan = $this->at('ditetapkan', ['surat_usulan_path' => 'usulan/s.pdf']);

        $this->actingAs($this->superAdmin)->post("/monitoring/{$usulan->id}/status", [
            'aksi' => 'selesaikan',
            'sertifikat' => $this->pdf('sertifikat.pdf'),
            'hasil_ukom' => 'Kompeten',
        ])->assertRedirect(route('monitoring.index'));

        $fresh = $usulan->fresh();
        $this->assertSame('selesai', $fresh->status_key);
        $this->assertSame('Kompeten', $fresh->hasil_ukom);
        Storage::disk('local')->assertExists($fresh->sertifikat_path);
        $this->assertSame(1, UsulanStatusLog::where('form_usulan_id', $usulan->id)->where('to_status', 'selesai')->count());
    }

    // FR-MON-4
    public function test_transition_without_file_is_rejected(): void
    {
        $a = $this->at('disetujui');
        $this->actingAs($this->timJf)->post("/monitoring/{$a->id}/status", ['aksi' => 'tetapkan'])->assertStatus(422);
        $this->assertSame('disetujui', $a->fresh()->status_key);

        $b = $this->at('ditetapkan');
        $this->actingAs($this->timJf)->post("/monitoring/{$b->id}/status", ['aksi' => 'selesaikan'])->assertStatus(422);
        $this->assertSame('ditetapkan', $b->fresh()->status_key);

        $this->assertSame(0, UsulanStatusLog::count());
    }

    // FR-MON-4
    public function test_non_pdf_file_is_rejected(): void
    {
        $usulan = $this->at('disetujui');

        $this->actingAs($this->timJf)->from('/monitoring')->post("/monitoring/{$usulan->id}/status", [
            'aksi' => 'tetapkan',
            'surat' => UploadedFile::fake()->create('surat.docx', 100, 'application/msword'),
        ])->assertSessionHasErrors('surat');

        $this->assertSame('disetujui', $usulan->fresh()->status_key);
        $this->assertCount(8, Storage::disk('local')->files('usulan/' . $usulan->id), 'hanya 8 berkas fixture, tidak ada surat tersimpan');
    }

    public static function disallowed(): array
    {
        return [
            'tetapkan saat verifikasi'   => ['verifikasi', 'tetapkan'],
            'tetapkan saat perbaikan'    => ['perbaikan', 'tetapkan'],
            'tetapkan saat ditetapkan'   => ['ditetapkan', 'tetapkan'],
            'tetapkan saat selesai'      => ['selesai', 'tetapkan'],
            'selesaikan saat verifikasi' => ['verifikasi', 'selesaikan'],
            'selesaikan saat disetujui'  => ['disetujui', 'selesaikan'],
            'selesaikan saat selesai'    => ['selesai', 'selesaikan'],
        ];
    }

    // FR-MON-2
    #[DataProvider('disallowed')]
    public function test_disallowed_transition_returns_422_and_changes_nothing(string $status, string $aksi): void
    {
        $usulan = $this->at($status);
        $before = $usulan->fresh()->toArray();

        $this->actingAs($this->timJf)->post("/monitoring/{$usulan->id}/status", [
            'aksi' => $aksi,
            'surat' => $this->pdf(),
            'sertifikat' => $this->pdf(),
        ])->assertStatus(422)->assertSee('data-section="transition-error"', false);

        $this->assertSame($before, $usulan->fresh()->toArray());
        $this->assertSame(0, UsulanStatusLog::count());
        $this->assertCount(8, Storage::disk('local')->files('usulan/' . $usulan->id), 'tidak ada file baru tersimpan');
    }

    // FR-MON-5
    public function test_super_admin_rollback_with_reason_is_logged(): void
    {
        $usulan = $this->at('ditetapkan', ['surat_usulan_path' => 'usulan/s.pdf']);

        $this->actingAs($this->superAdmin)->post("/monitoring/{$usulan->id}/status", [
            'aksi' => 'rollback',
            'ke_status' => 'disetujui',
            'alasan' => 'Nomor surat salah',
        ])->assertRedirect(route('monitoring.index'));

        $this->assertSame('disetujui', $usulan->fresh()->status_key);
        $log = UsulanStatusLog::where('form_usulan_id', $usulan->id)->sole();
        $this->assertSame(['ditetapkan', 'disetujui', 'Nomor surat salah', $this->superAdmin->id], [$log->from_status, $log->to_status, $log->catatan, (int) $log->actor_id]);
    }

    // FR-MON-5
    public function test_rollback_without_reason_is_rejected(): void
    {
        $usulan = $this->at('selesai');

        $this->actingAs($this->superAdmin)->post("/monitoring/{$usulan->id}/status", [
            'aksi' => 'rollback', 'ke_status' => 'ditetapkan', 'alasan' => '',
        ])->assertStatus(422);

        $this->assertSame('selesai', $usulan->fresh()->status_key);
    }

    // FR-MON-5
    public function test_tim_jf_cannot_rollback(): void
    {
        $usulan = $this->at('ditetapkan');

        $this->actingAs($this->timJf)->post("/monitoring/{$usulan->id}/status", [
            'aksi' => 'rollback', 'ke_status' => 'disetujui', 'alasan' => 'Alasan',
        ])->assertForbidden();

        $this->assertSame('ditetapkan', $usulan->fresh()->status_key);
        $this->assertSame(0, UsulanStatusLog::count());
    }

    public function test_rollback_form_is_shown_only_to_super_admin(): void
    {
        $this->at('ditetapkan');

        $this->actingAs($this->superAdmin)->get('/monitoring')->assertSee('data-form="rollback"', false);
        $this->actingAs($this->timJf)->get('/monitoring')->assertDontSee('data-form="rollback"', false);
    }

    public function test_replacing_surat_after_rollback_deletes_old_file(): void
    {
        $usulan = $this->at('disetujui');
        $this->actingAs($this->timJf)->post("/monitoring/{$usulan->id}/status", ['aksi' => 'tetapkan', 'surat' => $this->pdf()]);
        $oldPath = $usulan->fresh()->surat_usulan_path;

        $this->actingAs($this->superAdmin)->post("/monitoring/{$usulan->id}/status", ['aksi' => 'rollback', 'ke_status' => 'disetujui', 'alasan' => 'Revisi']);
        Storage::disk('local')->assertExists($oldPath);

        $this->actingAs($this->timJf)->post("/monitoring/{$usulan->id}/status", ['aksi' => 'tetapkan', 'surat' => $this->pdf()]);
        $newPath = $usulan->fresh()->surat_usulan_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);
    }

    public function test_pegawai_gets_403_on_monitoring_routes(): void
    {
        $pemilik = User::factory()->pegawai()->create();
        $usulan = $this->at('disetujui', [], $pemilik);

        $this->actingAs($pemilik)->get('/monitoring')->assertForbidden();
        $this->actingAs($pemilik)->post("/monitoring/{$usulan->id}/status", ['aksi' => 'tetapkan', 'surat' => $this->pdf()])->assertForbidden();
        $this->assertSame('disetujui', $usulan->fresh()->status_key);
    }

    // Keputusan 1 (rencana Fase 4): form status lama tidak mengubah apa pun
    public function test_old_status_route_changes_nothing_and_redirects(): void
    {
        $usulan = $this->at('verifikasi');

        $this->actingAs($this->timJf)->post("/admin/usulan/{$usulan->id}/status", ['status' => 5, 'catatan' => 'X'])
            ->assertRedirect(route('monitoring.index'))
            ->assertSessionHas('error', 'Perubahan status kini melalui Monitoring Progress UKOM.');

        $fresh = $usulan->fresh();
        $this->assertSame('verifikasi', $fresh->status_key);
        $this->assertSame(1, (int) $fresh->status);
        $this->assertNull($fresh->catatan);

        $this->actingAs($this->timJf)->get('/admin/monitoring?status=selesai')->assertRedirect('/monitoring?status=selesai');
        $this->actingAs($this->timJf)->get('/dashboard')->assertOk()->assertDontSee('name="status" class="select-status"', false);
    }
}
