<?php

namespace Tests\Feature\Phase1;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FR-RBAC-3: Tim Pembinaan JF dan Super Admin tidak dapat mengajukan usulan.
 */
class PengajuanRoleTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'aksi' => 'simpan',
            'jabatan_fungsional' => 'Statistisi',
            'jenis_usulan' => 'Inpassing',
            'nomor_surat' => '001/TEST',
            'hal_surat' => 'Uji',
            'satuan_kerja' => 'BPS RI',
        ];
    }

    public static function staffRoles(): array
    {
        return [
            'tim_jf' => [User::ROLE_TIM_JF],
            'super_admin' => [User::ROLE_SUPER_ADMIN],
        ];
    }

    #[DataProvider('staffRoles')]
    public function test_staff_post_to_pengajuan_returns_403(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->post('/pengajuan', $this->payload())->assertForbidden();
        $this->actingAs($user)->post('/form-usulan', $this->payload())->assertForbidden();
        $this->actingAs($user)->post('/pengajuan/kirim-ulang')->assertForbidden();

        $this->assertSame(0, FormUsulan::count());
    }

    public function test_pegawai_can_still_save_usulan(): void
    {
        $user = User::factory()->pegawai()->create();

        $this->actingAs($user)->post('/pengajuan', $this->payload())
            ->assertRedirect(route('pengajuan'));

        $this->assertSame(1, FormUsulan::where('user_id', $user->id)->count());
    }
}
