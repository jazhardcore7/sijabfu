<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-8, keputusan 2: Admin hanya mengusulkan pegawai aktif Provinsi/Kab/Kota di provinsinya.
 */
class AdminScopeTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_out_of_scope_targets_are_rejected_and_nothing_is_created(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();

        $targets = [
            'provinsi lain' => User::factory()->pegawai()->provinsi('33')->create(),
            'pusat' => User::factory()->pegawai()->pusat()->create(),
            'tanpa satker' => User::factory()->pegawai()->tanpaSatker()->create(),
            'nonaktif' => User::factory()->pegawai()->provinsi('32')->create(['is_active' => false]),
            'tim jf' => User::factory()->timJf()->create(['satker_tipe' => 'provinsi', 'provinsi_kode' => '32']),
            'admin satker lain' => User::factory()->adminSatker('32')->create(),
            'diri sendiri' => $admin,
        ];

        foreach ($targets as $label => $target) {
            $this->actingAs($admin)->get("/satker/pengajuan/baru/{$target->id}")->assertForbidden();
            $this->actingAs($admin)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $target->id, 'aksi' => 'kirim', 'berkas' => $this->allBerkas()]))
                ->assertForbidden();
            $this->actingAs($admin)->post("/satker/pengajuan/pegawai/{$target->id}/sinkron")->assertForbidden();
            $this->assertSame(0, FormUsulan::count(), $label);
        }

        $html = $this->actingAs($admin)->get('/satker/pengajuan/pilih-pegawai')->assertSee('data-total="0"', false);
        $this->assertNotNull($html);
    }

    public function test_admin_without_provinsi_has_empty_scope(): void
    {
        $admin = User::factory()->adminSatker('32')->create(['provinsi_kode' => null]);
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();

        $this->actingAs($admin)->get('/satker/pengajuan/pilih-pegawai')->assertOk()
            ->assertSee('data-section="provinsi-kosong"', false)->assertSee('data-total="0"', false);
        $this->actingAs($admin)->get("/satker/pengajuan/baru/{$pegawai->id}")->assertForbidden();
    }

    public function test_other_roles_cannot_use_satker_routes(): void
    {
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();

        foreach ([User::factory()->pegawai()->pusat()->create(), $pegawai, User::factory()->timJf()->create(), User::factory()->superAdmin()->create()] as $user) {
            $this->actingAs($user)->get('/satker/pengajuan')->assertForbidden();
            $this->actingAs($user)->get('/satker/pengajuan/pilih-pegawai')->assertForbidden();
            $this->actingAs($user)->get("/satker/pengajuan/baru/{$pegawai->id}")->assertForbidden();
            $this->actingAs($user)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $pegawai->id]))->assertForbidden();
        }
        $this->assertSame(0, FormUsulan::count());
    }
}
