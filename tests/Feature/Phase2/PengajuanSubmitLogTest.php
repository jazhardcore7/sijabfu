<?php

namespace Tests\Feature\Phase2;

use App\Models\FormUsulan;
use App\Models\UsulanStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FR-PU-4 (sebagian, Fase 2): kirim usulan membuat status verifikasi dan satu baris log.
 * Syarat semua slot berkas terisi dan notifikasi Tim JF ada di Fase 3 dan Fase 7.
 */
class PengajuanSubmitLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_creates_verifikasi_status_and_log(): void
    {
        $user = User::factory()->pegawai()->create();

        $this->actingAs($user)->post('/form-usulan', [
            'email' => $user->email,
            'jabatan_fungsional' => 'Statistisi',
            'jenis_usulan' => 'Inpassing',
            'nomor_surat' => '001/TEST',
            'hal_surat' => 'Uji',
            'nama_lengkap' => $user->name,
            'nip' => $user->nip,
            'satuan_kerja' => 'BPS RI',
            'link_berkas' => 'https://example.test/berkas',
        ])->assertRedirect(route('progress-berkas'));

        $usulan = FormUsulan::where('user_id', $user->id)->sole();
        $this->assertSame('verifikasi', $usulan->status_key);
        $this->assertSame(1, (int) $usulan->status, 'kolom status lama tetap diisi');
        $this->assertNotNull($usulan->submitted_at);

        $log = UsulanStatusLog::where('form_usulan_id', $usulan->id)->sole();
        $this->assertNull($log->from_status);
        $this->assertSame('verifikasi', $log->to_status);
        $this->assertSame($user->id, (int) $log->actor_id);
    }
}
