<?php

namespace Tests\Feature\Phase1;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * FR-RBAC-2 (bagian usulan): policy server-side untuk FormUsulan.
 * Policy berkas, pesan, dan file ditambahkan bersama modelnya di fase berikutnya.
 */
class FormUsulanPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function usulanFor(User $user): FormUsulan
    {
        return FormUsulan::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'jabatan_fungsional' => 'Statistisi',
            'jenis_usulan' => 'Inpassing',
            'nomor_surat' => '001/TEST',
            'hal_surat' => 'Uji',
            'nama_lengkap' => $user->name,
            'nip' => $user->nip,
            'satuan_kerja' => 'BPS RI',
            'link_berkas' => 'https://example.test/berkas',
            'status' => 1,
        ]);
    }

    public function test_pegawai_can_view_own_usulan_but_not_another_pegawai(): void
    {
        $a = User::factory()->pegawai()->create();
        $b = User::factory()->pegawai()->create();
        $usulanB = $this->usulanFor($b);

        $this->assertTrue(Gate::forUser($b)->allows('view', $usulanB));
        $this->assertFalse(Gate::forUser($a)->allows('view', $usulanB));
    }

    public function test_staff_can_view_any_usulan(): void
    {
        $usulan = $this->usulanFor(User::factory()->pegawai()->create());

        $this->assertTrue(Gate::forUser(User::factory()->timJf()->create())->allows('view', $usulan));
        $this->assertTrue(Gate::forUser(User::factory()->superAdmin()->create())->allows('view', $usulan));
    }

    public function test_only_pegawai_can_create_usulan(): void
    {
        $this->assertTrue(Gate::forUser(User::factory()->pegawai()->create())->allows('create', FormUsulan::class));
        $this->assertFalse(Gate::forUser(User::factory()->timJf()->create())->allows('create', FormUsulan::class));
        $this->assertFalse(Gate::forUser(User::factory()->superAdmin()->create())->allows('create', FormUsulan::class));
    }

    public function test_only_staff_can_update_status(): void
    {
        $usulan = $this->usulanFor(User::factory()->pegawai()->create());

        $this->assertFalse(Gate::forUser(User::factory()->pegawai()->create())->allows('updateStatus', $usulan));
        $this->assertTrue(Gate::forUser(User::factory()->timJf()->create())->allows('updateStatus', $usulan));
        $this->assertTrue(Gate::forUser(User::factory()->superAdmin()->create())->allows('updateStatus', $usulan));
    }
}
