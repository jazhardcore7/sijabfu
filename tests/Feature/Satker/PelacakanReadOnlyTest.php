<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-13: Pelacakan Status Pegawai Provinsi/Kabupaten/Kota read-only, dengan "Diajukan oleh".
 */
class PelacakanReadOnlyTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_pegawai_daerah_sees_tracker_without_edit_actions(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create(['name' => 'Admin Jabar Uji']);
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();
        $usulan = $this->usulanAt($pegawai, FormUsulan::STATUS_PERBAIKAN, ['submitted_by' => $admin->id, 'catatan' => 'SKP buram', 'hasil_verifikasi' => 'tms']);

        $html = $this->actingAs($pegawai)->get('/pelacakan-status')->assertOk()
            ->assertSee('data-section="perbaikan-banner"', false)
            ->assertSee('SKP buram')
            ->assertSee('data-hasil="tms"', false)
            ->assertSee('Diajukan oleh: Admin Jabar Uji (Admin Satuan Kerja Provinsi)')
            ->assertDontSee('data-action="perbaiki"', false)
            ->getContent();
        $this->assertStringNotContainsString('href="'.route('pengajuan').'"', $html);
        $this->assertStringContainsString('data-stage="verifikasi" data-state="perbaikan"', $html);
        $this->assertSame(FormUsulan::STATUS_PERBAIKAN, $usulan->fresh()->status_key);
    }

    public function test_pegawai_daerah_downloads_surat_under_unlock_rules(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('usulan/surat.pdf', '%PDF-1.4');
        $admin = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->kabkota('32', '73')->create();
        $usulan = $this->usulanAt($pegawai, FormUsulan::STATUS_DISETUJUI, ['submitted_by' => $admin->id, 'surat_usulan_path' => 'usulan/surat.pdf']);

        $this->actingAs($pegawai)->get('/pelacakan-status')->assertDontSee('data-action="download-surat"', false);
        $this->actingAs($pegawai)->get("/files/surat/{$usulan->id}")->assertForbidden();

        $usulan->forceFill(['status_key' => FormUsulan::STATUS_DITETAPKAN])->save();
        $this->actingAs($pegawai)->get('/pelacakan-status')->assertSee('data-action="download-surat"', false)->assertSee('data-action="preview-surat"', false);
        $this->actingAs($pegawai)->get("/files/surat/{$usulan->id}?mode=preview")->assertOk();
    }

    public function test_pegawai_pusat_keeps_perbaikan_button_and_sees_mandiri(): void
    {
        Storage::fake('local');
        $pegawai = User::factory()->pegawai()->pusat()->create();
        $this->usulanAt($pegawai, FormUsulan::STATUS_PERBAIKAN, ['submitted_by' => $pegawai->id]);

        $this->actingAs($pegawai)->get('/pelacakan-status')->assertOk()
            ->assertSee('Diajukan oleh: Mandiri')
            ->assertSee('data-action="perbaiki"', false)
            ->assertSee('href="'.route('pengajuan').'"', false);
    }
}
