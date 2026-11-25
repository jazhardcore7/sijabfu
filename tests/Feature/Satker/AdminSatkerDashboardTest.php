<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-5: dashboard Admin Satuan Kerja Provinsi hanya berisi usulan yang diajukannya.
 */
class AdminSatkerDashboardTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private function statCount(string $html, string $stat): ?int
    {
        return preg_match('/data-stat="'.$stat.'">.*?<div class="stat-count">(\d+)</s', $html, $m) ? (int) $m[1] : null;
    }

    public function test_dashboard_shows_only_own_submitted_usulan(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $adminLain = User::factory()->adminSatker('32')->create();
        $p1 = User::factory()->pegawai()->provinsi('32')->create(['name' => 'Pegawai Milik Admin']);
        $p2 = User::factory()->pegawai()->kabkota('32', '73')->create(['name' => 'Pegawai Draf Admin']);
        $p3 = User::factory()->pegawai()->provinsi('32')->create(['name' => 'Pegawai Admin Lain']);
        $pusat = User::factory()->pegawai()->create(['name' => 'Pegawai Pusat Mandiri']);

        $this->usulanAt($p1, FormUsulan::STATUS_PERBAIKAN, ['submitted_by' => $admin->id]);
        $this->usulanAt($p2, null, ['submitted_by' => $admin->id]);
        $this->usulanAt($p3, FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $adminLain->id]);
        $this->usulanAt($pusat, FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $pusat->id]);

        $html = $this->actingAs($admin)->get('/dashboard')->assertOk()
            ->assertSee('data-section="dashboard-admin-satker"', false)
            ->assertSee('Pegawai Milik Admin')
            ->assertSee('Pegawai Draf Admin')
            ->assertDontSee('Pegawai Admin Lain')
            ->assertDontSee('Pegawai Pusat Mandiri')
            ->assertDontSee('data-section="dashboard-grafik"', false)
            ->getContent();

        $this->assertSame(1, $this->statCount($html, 'total'));
        $this->assertSame(1, $this->statCount($html, 'perbaikan'));
        $this->assertSame(0, $this->statCount($html, 'verifikasi'));
        $this->assertSame(1, $this->statCount($html, 'draf'));
    }

    public function test_admin_without_provinsi_sees_warning(): void
    {
        $admin = User::factory()->adminSatker('32')->create(['provinsi_kode' => null]);

        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('data-section="provinsi-kosong"', false);
    }

    public function test_scope_helper_matches_decision_2(): void
    {
        $admin = User::factory()->adminSatker('32')->create();
        $in1 = User::factory()->pegawai()->provinsi('32')->create();
        $in2 = User::factory()->pegawai()->kabkota('32', '01')->create();
        User::factory()->pegawai()->provinsi('33')->create();
        User::factory()->pegawai()->create(); // pusat
        User::factory()->pegawai()->provinsi('32')->create(['is_active' => false]);
        User::factory()->adminSatker('32')->create();

        $this->assertEqualsCanonicalizing([$in1->id, $in2->id], User::inSatkerScopeOf($admin)->pluck('id')->all());
        $this->assertTrue($in1->isInSatkerScopeOf($admin));
        $this->assertSame(0, User::inSatkerScopeOf(User::factory()->timJf()->create())->count());
    }
}
