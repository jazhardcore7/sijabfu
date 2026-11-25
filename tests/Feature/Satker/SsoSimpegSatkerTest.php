<?php

namespace Tests\Feature\Satker;

use App\Contracts\SimpegDirectory;
use App\Models\SsoSyncLog;
use App\Models\User;
use App\Sso\SsoIdentity;
use App\Support\Satker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FR-SATKER-4: SSO sync dan SIMPEG sync mengisi satker, tidak mengubah role,
 * nilai kosong tidak menghapus data tersimpan.
 */
class SsoSimpegSatkerTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_rules(): void
    {
        $this->assertSame(['satker_kode' => '0000', 'satker_tipe' => 'pusat', 'provinsi_kode' => null], Satker::resolve(null, '0000', null));
        $this->assertSame(['satker_kode' => '3200', 'satker_tipe' => 'provinsi', 'provinsi_kode' => '32'], Satker::resolve(null, '3200', null));
        $this->assertSame(['satker_kode' => '3273', 'satker_tipe' => 'kabkota', 'provinsi_kode' => '32'], Satker::resolve(null, '3273', null));
        // satker_tipe eksplisit dari direktori diutamakan
        $this->assertSame('kabkota', Satker::resolve('kabkota', '3200', null)['satker_tipe']);
        $this->assertSame([], Satker::resolve(null, null, null));
        $this->assertSame([], Satker::resolve('', '  ', ''));
        $this->assertSame([], Satker::resolve('lainnya', 'abc', 'x'));
    }

    public function test_sso_sync_fills_satker_keeps_role_and_never_clears(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['nip' => '198001012005011001', 'email' => 'superadmin.jf@bps.go.id']);
        // Admin Satker yang sudah ada: role tetap walaupun direktori tidak mengenal role
        $admin = User::factory()->adminSatker('32')->create(['nip' => '199001012015011001', 'email' => 'admin.satker@example.test']);
        // Siti tanpa data satker di direktori: nilai tersimpan tidak dihapus
        $siti = User::factory()->pegawai()->create(['nip' => '199808182022032005', 'email' => 'siti.rahmawati@bps.go.id', 'satker_tipe' => 'pusat']);

        $this->actingAs($superAdmin)->post('/admin/sso-sync')->assertRedirect(route('admin.sso-sync'));

        $this->assertSame('admin_satker', $admin->fresh()->role);
        $this->assertSame('32', $admin->fresh()->provinsi_kode);
        $this->assertSame('pusat', $siti->fresh()->satker_tipe);

        $dua = User::where('nip', '200002022024012002')->sole();
        $this->assertSame(['pegawai', 'kabkota', '3273', '32'], [$dua->role, $dua->satker_tipe, $dua->satker_kode, $dua->provinsi_kode]);

        // User baru tanpa data satker tetap null dan dihitung di log (keputusan 1)
        $this->assertNull(User::where('nip', '200004042024014004')->value('satker_tipe'));
        $this->assertSame(1, SsoSyncLog::sole()->satker_missing_count);

        $this->actingAs($superAdmin)->get('/admin/sso-sync')->assertSee('data-satker-missing="1"', false);
    }

    public function test_simpeg_sync_fills_satker_of_user(): void
    {
        $pegawai = User::factory()->pegawai()->tanpaSatker()->create(['nip' => '200001012024011001']);

        $this->actingAs($pegawai)->post('/pengajuan/sinkron')->assertSessionHas('success');

        $pegawai->refresh();
        $this->assertSame(['provinsi', '3200', '32', 'pegawai'], [$pegawai->satker_tipe, $pegawai->satker_kode, $pegawai->provinsi_kode, $pegawai->role]);
    }

    public function test_simpeg_without_satker_data_keeps_stored_values(): void
    {
        $pegawai = User::factory()->pegawai()->kabkota('32', '73')->create(['nip' => '555']);
        $this->app->instance(SimpegDirectory::class, new class implements SimpegDirectory
        {
            public function findByNip(string $nip): ?SsoIdentity
            {
                return new SsoIdentity(nip: $nip, name: 'Nama Simpeg');
            }
        });

        $this->actingAs($pegawai)->post('/pengajuan/sinkron')->assertSessionHas('success');

        $pegawai->refresh();
        $this->assertSame(['Nama Simpeg', 'kabkota', '3273', '32'], [$pegawai->name, $pegawai->satker_tipe, $pegawai->satker_kode, $pegawai->provinsi_kode]);
    }
}
