<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanStatusLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-14: Pelacakan Status Admin Satuan Kerja Provinsi (daftar dan detail).
 */
class AdminTrackerTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->adminSatker('32')->create();
    }

    private function usulanFor(string $name, ?string $status, ?User $submitter = null): FormUsulan
    {
        $pegawai = User::factory()->pegawai()->provinsi('32')->create(['name' => $name]);

        return $this->usulanAt($pegawai, $status, ['submitted_by' => ($submitter ?? $this->admin)->id, 'submitted_at' => $status ? now() : null]);
    }

    public function test_list_shows_only_own_submitted_forms_with_filters(): void
    {
        $this->usulanFor('Pegawai Verifikasi Uji', FormUsulan::STATUS_VERIFIKASI);
        $this->usulanFor('Pegawai Selesai Uji', FormUsulan::STATUS_SELESAI);
        $this->usulanFor('Pegawai Draf Uji', null);
        $this->usulanFor('Pegawai Admin Lain Uji', FormUsulan::STATUS_VERIFIKASI, User::factory()->adminSatker('32')->create());

        $this->actingAs($this->admin)->get('/dashboard')->assertSee('data-menu-route="satker.pelacakan.index"', false);

        $this->actingAs($this->admin)->get('/satker/pelacakan')->assertOk()
            ->assertSee('data-total="2"', false)
            ->assertSee('Pegawai Verifikasi Uji')->assertSee('Pegawai Selesai Uji')
            ->assertDontSee('Pegawai Draf Uji')->assertDontSee('Pegawai Admin Lain Uji')
            ->assertSee('100% selesai');

        $this->actingAs($this->admin)->get('/satker/pelacakan?status=selesai')
            ->assertSee('Pegawai Selesai Uji')->assertDontSee('Pegawai Verifikasi Uji');
        $this->actingAs($this->admin)->get('/satker/pelacakan?q=Verifikasi&status=selesai')->assertSee('data-total="0"', false);
        $this->actingAs($this->admin)->get('/satker/pelacakan?q=Verifikasi')->assertSee('data-total="1"', false);
    }

    public function test_list_paginates_by_15(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            $this->usulanFor('Pegawai Halaman '.$i, FormUsulan::STATUS_VERIFIKASI);
        }

        $page1 = $this->actingAs($this->admin)->get('/satker/pelacakan')->getContent();
        $this->assertSame(15, substr_count($page1, 'data-usulan="'));
        $page2 = $this->actingAs($this->admin)->get('/satker/pelacakan?page=2')->getContent();
        $this->assertSame(1, substr_count($page2, 'data-usulan="'));
    }

    public function test_detail_shows_tracker_timeline_and_perbaikan_link(): void
    {
        $usulan = $this->usulanFor('Pegawai Detail Uji', FormUsulan::STATUS_PERBAIKAN);
        $timJf = User::factory()->timJf()->create();
        UsulanStatusLog::create(['form_usulan_id' => $usulan->id, 'actor_id' => $this->admin->id, 'from_status' => null, 'to_status' => 'verifikasi']);
        UsulanStatusLog::create(['form_usulan_id' => $usulan->id, 'actor_id' => $timJf->id, 'from_status' => 'verifikasi', 'to_status' => 'perbaikan', 'catatan' => 'Lengkapi SKP']);

        $this->actingAs($this->admin)->get("/satker/pelacakan/{$usulan->id}")->assertOk()
            ->assertSee('data-section="timeline"', false)
            ->assertSee('oleh Admin Satuan Kerja Provinsi')
            ->assertSee('oleh Tim Pembinaan JF')
            ->assertSee('Lengkapi SKP')
            ->assertSee('data-action="perbaiki"', false)
            ->assertSee('href="'.route('satker.pengajuan.edit', $usulan).'"', false);
    }

    public function test_detail_is_403_for_other_admin_and_404_for_draft(): void
    {
        $usulan = $this->usulanFor('Pegawai Lain', FormUsulan::STATUS_VERIFIKASI);
        $draf = $this->usulanFor('Pegawai Draf', null);

        $this->actingAs(User::factory()->adminSatker('32')->create())->get("/satker/pelacakan/{$usulan->id}")->assertForbidden();
        $this->actingAs(User::factory()->timJf()->create())->get("/satker/pelacakan/{$usulan->id}")->assertForbidden();
        $this->actingAs($this->admin)->get("/satker/pelacakan/{$draf->id}")->assertNotFound();
        $this->actingAs($this->admin)->get("/satker/pelacakan/{$usulan->id}")->assertOk()->assertDontSee('data-action="perbaiki"', false);
    }
}
