<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-12: file usulan untuk Admin pengaju, dengan aturan buka yang sama seperti Pegawai.
 */
class AdminFileAccessTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_submitting_admin_can_open_berkas_other_admin_cannot(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $adminLain = User::factory()->adminSatker('32')->create();
        $usulan = $this->usulanAt(User::factory()->pegawai()->provinsi('32')->create(), FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $admin->id]);
        $berkas = $usulan->berkas()->first();

        $this->actingAs($admin)->get("/files/berkas/{$berkas->id}?mode=preview")->assertOk();
        $this->actingAs($adminLain)->get("/files/berkas/{$berkas->id}")->assertForbidden();
        $this->actingAs(User::factory()->pegawai()->provinsi('32')->create())->get("/files/berkas/{$berkas->id}")->assertForbidden();
    }

    public function test_surat_and_sertifikat_follow_unlock_rules_for_admin_and_pegawai(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $adminLain = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();
        Storage::disk('local')->put('usulan/surat.pdf', '%PDF-1.4 surat');
        Storage::disk('local')->put('usulan/sertifikat.pdf', '%PDF-1.4 sertifikat');
        $usulan = $this->usulanAt($pegawai, FormUsulan::STATUS_DISETUJUI, [
            'submitted_by' => $admin->id,
            'surat_usulan_path' => 'usulan/surat.pdf',
            'sertifikat_path' => 'usulan/sertifikat.pdf',
        ]);

        foreach ([$admin, $pegawai] as $user) {
            $this->actingAs($user)->get("/files/surat/{$usulan->id}")->assertForbidden();
        }

        $usulan->forceFill(['status_key' => FormUsulan::STATUS_DITETAPKAN])->save();
        foreach ([$admin, $pegawai] as $user) {
            $this->actingAs($user)->get("/files/surat/{$usulan->id}")->assertOk();
            $this->actingAs($user)->get("/files/sertifikat/{$usulan->id}")->assertForbidden();
        }
        $this->actingAs($adminLain)->get("/files/surat/{$usulan->id}")->assertForbidden();

        $usulan->forceFill(['status_key' => FormUsulan::STATUS_SELESAI])->save();
        foreach ([$admin, $pegawai] as $user) {
            $this->actingAs($user)->get("/files/sertifikat/{$usulan->id}?mode=preview")->assertOk();
        }
    }
}
