<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-18: kolom "Diajukan oleh" dan filter satker/provinsi di daftar Tim JF dan Super Admin.
 */
class StaffListSatkerTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $timJf;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->timJf = User::factory()->timJf()->create();

        $pusat = User::factory()->pegawai()->pusat()->create(['name' => 'Pegawai Pusat Mandiri']);
        $this->usulanAt($pusat, FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $pusat->id, 'nama_lengkap' => 'Pegawai Pusat Mandiri', 'submitted_at' => now()]);

        $admin32 = User::factory()->adminSatker('32')->create(['name' => 'Admin Jabar']);
        $jabar = User::factory()->pegawai()->provinsi('32')->create(['name' => 'Pegawai Jabar']);
        $this->usulanAt($jabar, FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $admin32->id, 'nama_lengkap' => 'Pegawai Jabar', 'submitted_at' => now()]);

        $admin33 = User::factory()->adminSatker('33')->create(['name' => 'Admin Jateng']);
        $kota33 = User::factory()->pegawai()->kabkota('33', '74')->create(['name' => 'Pegawai Kota Jateng']);
        $this->usulanAt($kota33, FormUsulan::STATUS_PERBAIKAN, ['submitted_by' => $admin33->id, 'nama_lengkap' => 'Pegawai Kota Jateng', 'submitted_at' => now()]);
    }

    public function test_verifikasi_column_and_filters(): void
    {
        $this->actingAs($this->timJf)->get('/verifikasi')->assertOk()
            ->assertSee('<th>Diajukan oleh</th>', false)
            ->assertSee('Mandiri')
            ->assertSee('Admin Jabar (Admin Satuan Kerja Provinsi)')
            ->assertSee('data-filter="satker"', false)
            ->assertSee('data-total="3"', false);

        $this->actingAs($this->timJf)->get('/verifikasi?satker=provinsi&provinsi=32')
            ->assertSee('Pegawai Jabar')->assertDontSee('Pegawai Pusat Mandiri')->assertDontSee('Pegawai Kota Jateng');
        $this->actingAs($this->timJf)->get('/verifikasi?provinsi=33')->assertSee('data-total="1"', false)->assertSee('Pegawai Kota Jateng');
        $this->actingAs($this->timJf)->get('/verifikasi?satker=pusat')->assertSee('data-total="1"', false)->assertSee('Pegawai Pusat Mandiri');
        // digabung dengan filter status dan pencarian
        $this->actingAs($this->timJf)->get('/verifikasi?provinsi=33&status=verifikasi')->assertSee('data-total="0"', false);
        $this->actingAs($this->timJf)->get('/verifikasi?satker=provinsi&q=Jabar')->assertSee('data-total="1"', false);
        // nilai tidak dikenal diabaikan
        $this->actingAs($this->timJf)->get('/verifikasi?satker=lain&provinsi=99')->assertSee('data-total="3"', false);
    }

    public function test_verifikasi_detail_shows_diajukan_oleh(): void
    {
        $usulan = FormUsulan::where('nama_lengkap', 'Pegawai Jabar')->sole();

        $this->actingAs($this->timJf)->get("/verifikasi/{$usulan->id}")->assertOk()
            ->assertSee('data-section="diajukan-oleh"', false)->assertSee('Admin Jabar (Admin Satuan Kerja Provinsi)');
    }

    public function test_monitoring_counts_follow_satker_filter(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $html = $this->actingAs($superAdmin)->get('/monitoring?satker=kabkota')->assertOk()
            ->assertSee('Pegawai Kota Jateng')->assertDontSee('Pegawai Jabar')
            ->assertSee('Diajukan oleh:</strong> Admin Jateng', false)
            ->getContent();
        $this->assertMatchesRegularExpression('/data-stat="total".*?<div class="stat-count">1</s', $html);
        $this->assertStringContainsString('data-count="1"', $html);

        $all = $this->actingAs($superAdmin)->get('/monitoring')->getContent();
        $this->assertMatchesRegularExpression('/data-stat="total".*?<div class="stat-count">3</s', $all);
        // tautan kartu status mempertahankan filter satker
        $this->assertStringContainsString('satker=kabkota', $html);
    }

    public function test_staff_dashboard_table_shows_diajukan_oleh(): void
    {
        $this->actingAs($this->timJf)->get('/dashboard')->assertOk()
            ->assertSee('Diajukan oleh: Mandiri')
            ->assertSee('Diajukan oleh: Admin Jateng (Admin Satuan Kerja Provinsi)');
    }
}
