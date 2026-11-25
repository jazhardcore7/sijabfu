<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use App\Policies\FormUsulanPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-6: Pegawai Provinsi/Kabupaten/Kota (dan Pegawai tanpa satker) tidak dapat mengajukan sendiri.
 * Keputusan 11: usulan aktif yang diajukannya sendiri sebelum perubahan tetap dapat diselesaikan.
 */
class PengajuanBlockTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public static function blockedPegawai(): array
    {
        return [
            'provinsi' => ['provinsi', FormUsulanPolicy::PESAN_DAERAH],
            'kabkota' => ['kabkota', FormUsulanPolicy::PESAN_DAERAH],
            'tanpa satker' => ['tanpaSatker', FormUsulanPolicy::PESAN_SATKER_KOSONG],
        ];
    }

    #[DataProvider('blockedPegawai')]
    public function test_every_pengajuan_route_returns_403_and_writes_nothing(string $state, string $message): void
    {
        Storage::fake('local');
        $pegawai = User::factory()->pegawai()->{$state}()->create();
        $before = $pegawai->only(['name', 'jabatan', 'unit_kerja']);

        $this->actingAs($pegawai)->get('/pengajuan')->assertForbidden()->assertSee($message);
        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan(['aksi' => 'kirim', 'berkas' => $this->allBerkas()]))->assertForbidden();
        $this->actingAs($pegawai)->post('/form-usulan', $this->dataUsulan())->assertForbidden();
        $this->actingAs($pegawai)->post('/pengajuan/kirim-ulang')->assertForbidden();
        $this->actingAs($pegawai)->post('/pengajuan/sinkron')->assertForbidden();

        $this->assertSame(0, FormUsulan::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame($before, $pegawai->fresh()->only(['name', 'jabatan', 'unit_kerja']));
    }

    public function test_menu_dashboard_and_pelacakan_hide_pengajuan_for_pegawai_daerah(): void
    {
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();

        $dashboard = $this->actingAs($pegawai)->get('/dashboard')->assertOk()->getContent();
        $this->assertStringNotContainsString('data-menu-route="pengajuan"', $dashboard);
        $this->assertStringNotContainsString('href="'.route('pengajuan').'"', $dashboard);
        $this->assertStringContainsString('data-menu-route="pelacakan-status"', $dashboard);
        $this->assertStringContainsString('Usulan Anda diajukan oleh Admin Satuan Kerja.', $dashboard);

        $pelacakan = $this->actingAs($pegawai)->get('/pelacakan-status')->assertOk()->getContent();
        $this->assertStringNotContainsString('href="'.route('pengajuan').'"', $pelacakan);
    }

    public function test_pegawai_pusat_still_sees_pengajuan(): void
    {
        $pegawai = User::factory()->pegawai()->pusat()->create();

        $this->actingAs($pegawai)->get('/dashboard')->assertSee('data-menu-route="pengajuan"', false)->assertSee('Ajukan usulan');
        $this->actingAs($pegawai)->get('/pengajuan')->assertOk()->assertSee('id="formPengajuan"', false);
    }

    public function test_pegawai_daerah_with_admin_submitted_usulan_cannot_open_pengajuan(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();
        $usulan = $this->usulanAt($pegawai, FormUsulan::STATUS_PERBAIKAN, ['submitted_by' => $admin->id]);

        $this->actingAs($pegawai)->get('/pengajuan')->assertForbidden();
        $this->actingAs($pegawai)->post('/pengajuan/kirim-ulang')->assertForbidden();
        $this->assertSame(FormUsulan::STATUS_PERBAIKAN, $usulan->fresh()->status_key);

        // Pelacakan tetap dapat dibuka, tanpa tombol perbaikan
        $this->actingAs($pegawai)->get('/pelacakan-status')->assertOk()
            ->assertSee('data-section="perbaikan-banner"', false)
            ->assertDontSee('Perbaiki dan Kirim Ulang');
    }

    // Keputusan 11
    public function test_grandfathered_self_submitted_usulan_can_be_finished_then_blocked(): void
    {
        Storage::fake('local');
        $pegawai = User::factory()->pegawai()->provinsi('31')->create();
        $usulan = $this->usulanAt($pegawai, FormUsulan::STATUS_PERBAIKAN, ['submitted_by' => $pegawai->id]);

        $this->actingAs($pegawai)->get('/pengajuan')->assertOk()->assertSee('data-action="kirim-ulang"', false);
        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan(['berkas' => ['skp' => $this->pdf('skp-baru.pdf')]]))->assertRedirect(route('pengajuan'));
        $this->actingAs($pegawai)->post('/pengajuan/kirim-ulang')->assertRedirect(route('pelacakan-status'));
        $this->assertSame(FormUsulan::STATUS_VERIFIKASI, $usulan->fresh()->status_key);

        // Setelah selesai, usulan baru tidak boleh dibuat sendiri
        $usulan->forceFill(['status_key' => FormUsulan::STATUS_SELESAI])->save();
        $this->actingAs($pegawai)->get('/pengajuan')->assertForbidden();
        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan())->assertForbidden();
        $this->assertSame(1, FormUsulan::count());
    }
}
