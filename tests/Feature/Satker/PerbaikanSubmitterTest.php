<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanBerkas;
use App\Services\UsulanStateMachine;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-11 dan FR-SATKER-12, keputusan 4 dan 5: perbaikan dan kirim ulang hanya oleh pengaju;
 * Admin hanya melihat dan mengubah usulan yang diajukannya.
 */
class PerbaikanSubmitterTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $admin;

    private User $adminLain;

    private User $pegawai;

    private FormUsulan $usulan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->adminSatker('32')->create();
        $this->adminLain = User::factory()->adminSatker('32')->create();
        $this->pegawai = User::factory()->pegawai()->provinsi('32')->create();
        $this->usulan = $this->usulanAt($this->pegawai, FormUsulan::STATUS_PERBAIKAN, ['submitted_by' => $this->admin->id, 'catatan' => 'SKP buram']);
        $this->usulan->berkas()->where('jenis_persyaratan', 'skp')->update(['pemeriksaan' => UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN]);
    }

    public function test_submitting_admin_fixes_files_and_resubmits(): void
    {
        $this->actingAs($this->admin)->get("/satker/pengajuan/{$this->usulan->id}")->assertOk()
            ->assertSee('data-section="perbaikan-banner"', false)->assertSee('SKP buram')->assertSee('data-action="kirim-ulang"', false);

        $this->actingAs($this->admin)->post("/satker/pengajuan/{$this->usulan->id}", $this->dataUsulan(['berkas' => ['skp' => $this->pdf('skp-baru.pdf')]]))
            ->assertRedirect(route('satker.pengajuan.edit', $this->usulan));
        $this->assertNull($this->usulan->berkas()->where('jenis_persyaratan', 'skp')->value('pemeriksaan'));

        $this->actingAs($this->admin)->post("/satker/pengajuan/{$this->usulan->id}/kirim-ulang")->assertRedirect(route('satker.pengajuan.index'));
        $this->assertSame(FormUsulan::STATUS_VERIFIKASI, $this->usulan->fresh()->status_key);
        $log = $this->usulan->statusLogs()->get()->last();
        $this->assertSame(['perbaikan', 'verifikasi', $this->admin->id], [$log->from_status, $log->to_status, (int) $log->actor_id]);
    }

    public function test_other_admin_gets_403_everywhere(): void
    {
        $id = $this->usulan->id;
        $this->actingAs($this->adminLain)->get("/satker/pengajuan/{$id}")->assertForbidden();
        $this->actingAs($this->adminLain)->post("/satker/pengajuan/{$id}", $this->dataUsulan())->assertForbidden();
        $this->actingAs($this->adminLain)->post("/satker/pengajuan/{$id}/kirim-ulang")->assertForbidden();
        $this->actingAs($this->adminLain)->get('/satker/pengajuan')->assertDontSee('data-usulan="'.$id.'"', false);

        $this->assertSame(FormUsulan::STATUS_PERBAIKAN, $this->usulan->fresh()->status_key);
    }

    public function test_proposed_pegawai_daerah_cannot_resubmit(): void
    {
        $this->actingAs($this->pegawai)->post('/pengajuan/kirim-ulang')->assertForbidden();

        $this->expectException(AuthorizationException::class);
        try {
            app(UsulanStateMachine::class)->resubmit($this->usulan, $this->pegawai);
        } finally {
            $this->assertSame(FormUsulan::STATUS_PERBAIKAN, $this->usulan->fresh()->status_key);
        }
    }

    public function test_state_machine_rejects_non_submitters(): void
    {
        foreach ([$this->adminLain, User::factory()->timJf()->create(), User::factory()->superAdmin()->create()] as $actor) {
            try {
                app(UsulanStateMachine::class)->resubmit($this->usulan, $actor);
                $this->fail('resubmit oleh bukan pengaju harus ditolak');
            } catch (AuthorizationException) {
                $this->assertSame(FormUsulan::STATUS_PERBAIKAN, $this->usulan->fresh()->status_key);
            }
        }
    }
}
