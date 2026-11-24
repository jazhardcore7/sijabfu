<?php

namespace Tests\Feature\Phase3;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PRD Revisi bagian 7: route lama dialihkan ke padanan barunya.
 */
class OldRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_routes_redirect_to_new_equivalents(): void
    {
        $pegawai = User::factory()->pegawai()->create();

        $this->actingAs($pegawai)->get('/form-usulan')->assertRedirect('/pengajuan');
        $this->actingAs($pegawai)->get('/progress-berkas')->assertRedirect('/pelacakan-status');
        $this->actingAs($pegawai)->get('/uji-kompetensi')->assertRedirect('/pelacakan-status');
    }

    public function test_old_post_route_still_saves(): void
    {
        $pegawai = User::factory()->pegawai()->create();

        $this->actingAs($pegawai)->post('/form-usulan', [
            'jabatan_fungsional' => 'Statistisi',
            'jenis_usulan' => 'Inpassing',
            'nomor_surat' => '001',
            'hal_surat' => 'Uji',
            'satuan_kerja' => 'BPS',
        ])->assertRedirect(route('pengajuan'));
    }
}
