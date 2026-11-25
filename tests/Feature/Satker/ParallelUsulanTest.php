<?php

namespace Tests\Feature\Satker;

use App\Http\Controllers\SatkerPengajuanController;
use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-9, keputusan 3: banyak usulan berjalan untuk Admin, satu usulan aktif per pegawai.
 */
class ParallelUsulanTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_admin_can_run_several_forms_but_one_active_per_pegawai(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        [$a, $b, $c] = User::factory()->pegawai()->provinsi('32')->count(3)->create()->all();

        $this->usulanAt($a, FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $admin->id]);
        $this->usulanAt($b, FormUsulan::STATUS_PERBAIKAN, ['submitted_by' => $admin->id]);

        // Pegawai C: boleh, walaupun Admin sudah punya 2 usulan dalam proses
        $this->actingAs($admin)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $c->id, 'aksi' => 'kirim', 'berkas' => $this->allBerkas()]))
            ->assertRedirect(route('satker.pengajuan.index'));
        $this->assertSame(3, FormUsulan::where('submitted_by', $admin->id)->count());

        // Pegawai A sudah punya usulan aktif
        $this->actingAs($admin)->get("/satker/pengajuan/baru/{$a->id}")
            ->assertRedirect(route('satker.pengajuan.pilih-pegawai'))->assertSessionHas('error', SatkerPengajuanController::PESAN_AKTIF);
        $this->actingAs($admin)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $a->id]))
            ->assertRedirect(route('satker.pengajuan.pilih-pegawai'))->assertSessionHas('error', SatkerPengajuanController::PESAN_AKTIF);
        $this->assertSame(1, FormUsulan::where('user_id', $a->id)->count());

        $this->actingAs($admin)->get('/satker/pengajuan/pilih-pegawai')->assertSee('Sudah ada usulan aktif');
    }

    public function test_active_usulan_of_other_admin_also_blocks(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $adminLain = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();
        $this->usulanAt($pegawai, null, ['submitted_by' => $adminLain->id]);

        $this->actingAs($admin)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $pegawai->id]))
            ->assertSessionHas('error', SatkerPengajuanController::PESAN_AKTIF);
        $this->assertSame(1, FormUsulan::count());
    }

    public function test_new_usulan_allowed_after_previous_is_selesai(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();
        $this->usulanAt($pegawai, FormUsulan::STATUS_SELESAI, ['submitted_by' => $admin->id]);

        $this->actingAs($admin)->get("/satker/pengajuan/baru/{$pegawai->id}")->assertOk();
        $this->actingAs($admin)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $pegawai->id]))->assertRedirect();
        $this->assertSame(2, FormUsulan::where('user_id', $pegawai->id)->count());
    }
}
