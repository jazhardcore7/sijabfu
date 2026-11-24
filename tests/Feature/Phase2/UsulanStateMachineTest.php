<?php

namespace Tests\Feature\Phase2;

use App\Exceptions\InvalidStatusTransition;
use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanBerkas;
use App\Models\UsulanStatusLog;
use App\Services\UsulanStateMachine;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * State machine PRD Revisi bagian 4.
 * FR-MON-2, FR-MON-3, FR-MON-4, FR-MON-5, FR-VB-4, FR-VB-5, FR-PU-5 (level service).
 */
class UsulanStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private UsulanStateMachine $sm;

    private User $pegawai;

    private User $timJf;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sm = new UsulanStateMachine;
        $this->pegawai = User::factory()->pegawai()->create();
        $this->timJf = User::factory()->timJf()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
    }

    private function usulan(?string $statusKey = null, ?User $owner = null): FormUsulan
    {
        $owner ??= $this->pegawai;

        return FormUsulan::create([
            'user_id' => $owner->id,
            'email' => $owner->email,
            'jabatan_fungsional' => 'Statistisi',
            'jenis_usulan' => 'Inpassing',
            'nomor_surat' => '001/TEST',
            'hal_surat' => 'Uji',
            'nama_lengkap' => $owner->name,
            'nip' => $owner->nip,
            'satuan_kerja' => 'BPS RI',
            'link_berkas' => 'https://example.test/berkas',
            'status' => 1,
            'status_key' => $statusKey,
        ]);
    }

    private function assertLogged(FormUsulan $usulan, ?string $from, string $to, User $actor, ?string $catatan = null): void
    {
        $logs = UsulanStatusLog::where('form_usulan_id', $usulan->id)->get();
        $this->assertCount(1, $logs);
        $this->assertSame($from, $logs[0]->from_status);
        $this->assertSame($to, $logs[0]->to_status);
        $this->assertSame($actor->id, (int) $logs[0]->actor_id);
        $this->assertSame($catatan, $logs[0]->catatan);
        $this->assertNotNull($logs[0]->created_at);
    }

    private function assertUnchanged(FormUsulan $usulan, ?string $statusKey): void
    {
        $this->assertSame($statusKey, $usulan->fresh()->status_key);
        $this->assertSame(0, UsulanStatusLog::where('form_usulan_id', $usulan->id)->count());
    }

    // ---- transisi yang diizinkan (FR-MON-3: setiap perubahan menulis log) ----

    public function test_submit_sets_verifikasi_and_logs(): void
    {
        $usulan = $this->sm->submit($this->usulan(), $this->pegawai);

        $this->assertSame('verifikasi', $usulan->fresh()->status_key);
        $this->assertNotNull($usulan->fresh()->submitted_at);
        $this->assertLogged($usulan, null, 'verifikasi', $this->pegawai);
    }

    public function test_ms_decision_sets_disetujui(): void
    {
        $usulan = $this->sm->decide($this->usulan('verifikasi'), $this->timJf, 'ms');

        $fresh = $usulan->fresh();
        $this->assertSame('disetujui', $fresh->status_key);
        $this->assertSame('ms', $fresh->hasil_verifikasi);
        $this->assertSame($this->timJf->id, (int) $fresh->verified_by);
        $this->assertNotNull($fresh->verified_at);
        $this->assertLogged($usulan, 'verifikasi', 'disetujui', $this->timJf);
    }

    public function test_tms_decision_with_catatan_sets_perbaikan(): void
    {
        $usulan = $this->sm->decide($this->usulan('verifikasi'), $this->superAdmin, 'tms', 'SKP belum ditandatangani');

        $fresh = $usulan->fresh();
        $this->assertSame('perbaikan', $fresh->status_key);
        $this->assertSame('tms', $fresh->hasil_verifikasi);
        $this->assertSame('SKP belum ditandatangani', $fresh->catatan);
        $this->assertLogged($usulan, 'verifikasi', 'perbaikan', $this->superAdmin, 'SKP belum ditandatangani');
    }

    public function test_tms_decision_with_flagged_berkas_and_no_catatan_is_allowed(): void
    {
        $usulan = $this->usulan('verifikasi');
        UsulanBerkas::create([
            'form_usulan_id' => $usulan->id,
            'jenis_persyaratan' => 'SKP',
            'file_path' => 'berkas/x.pdf',
            'original_name' => 'skp.pdf',
            'mime' => 'application/pdf',
            'size' => 100,
            'pemeriksaan' => UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN,
        ]);

        $this->sm->decide($usulan, $this->timJf, 'tms');

        $this->assertSame('perbaikan', $usulan->fresh()->status_key);
    }

    public function test_tms_without_reason_is_rejected(): void
    {
        $usulan = $this->usulan('verifikasi');

        try {
            $this->sm->decide($usulan, $this->timJf, 'tms', '   ');
            $this->fail('TMS tanpa alasan harus ditolak');
        } catch (InvalidStatusTransition) {
        }

        $this->assertUnchanged($usulan, 'verifikasi');
    }

    public function test_resubmit_from_perbaikan_sets_verifikasi(): void
    {
        $usulan = $this->sm->resubmit($this->usulan('perbaikan'), $this->pegawai);

        $this->assertSame('verifikasi', $usulan->fresh()->status_key);
        $this->assertLogged($usulan, 'perbaikan', 'verifikasi', $this->pegawai);
    }

    public function test_issue_surat_with_file_sets_ditetapkan(): void
    {
        $usulan = $this->sm->issueSurat($this->usulan('disetujui'), $this->timJf, 'surat/abc.pdf');

        $this->assertSame('ditetapkan', $usulan->fresh()->status_key);
        $this->assertSame('surat/abc.pdf', $usulan->fresh()->surat_usulan_path);
        $this->assertLogged($usulan, 'disetujui', 'ditetapkan', $this->timJf);
    }

    public function test_complete_with_sertifikat_sets_selesai(): void
    {
        $usulan = $this->sm->complete($this->usulan('ditetapkan'), $this->superAdmin, 'sertifikat/abc.pdf', 'Lulus');

        $fresh = $usulan->fresh();
        $this->assertSame('selesai', $fresh->status_key);
        $this->assertSame('sertifikat/abc.pdf', $fresh->sertifikat_path);
        $this->assertSame('Lulus', $fresh->hasil_ukom);
        $this->assertLogged($usulan, 'ditetapkan', 'selesai', $this->superAdmin);
    }

    public function test_complete_without_hasil_ukom_is_allowed(): void
    {
        $usulan = $this->sm->complete($this->usulan('ditetapkan'), $this->timJf, 'sertifikat/abc.pdf');

        $this->assertSame('selesai', $usulan->fresh()->status_key);
        $this->assertNull($usulan->fresh()->hasil_ukom);
    }

    // ---- FR-MON-4: file wajib ----

    public function test_issue_surat_without_file_is_rejected(): void
    {
        $usulan = $this->usulan('disetujui');

        $this->expectException(InvalidStatusTransition::class);
        try {
            $this->sm->issueSurat($usulan, $this->timJf, null);
        } finally {
            $this->assertUnchanged($usulan, 'disetujui');
        }
    }

    public function test_complete_without_sertifikat_is_rejected(): void
    {
        $usulan = $this->usulan('ditetapkan');

        $this->expectException(InvalidStatusTransition::class);
        try {
            $this->sm->complete($usulan, $this->timJf, '');
        } finally {
            $this->assertUnchanged($usulan, 'ditetapkan');
        }
    }

    // ---- FR-MON-2 dan FR-VB-5: transisi yang tidak diizinkan ----

    public static function disallowed(): array
    {
        // [status sekarang, aksi]
        return [
            'submit ulang usulan yang sudah ada' => ['verifikasi', 'submit'],
            'keputusan saat perbaikan' => ['perbaikan', 'decide'],
            'keputusan saat disetujui' => ['disetujui', 'decide'],
            'keputusan saat ditetapkan' => ['ditetapkan', 'decide'],
            'keputusan saat selesai' => ['selesai', 'decide'],
            'kirim ulang saat verifikasi' => ['verifikasi', 'resubmit'],
            'kirim ulang saat disetujui' => ['disetujui', 'resubmit'],
            'surat saat verifikasi' => ['verifikasi', 'issueSurat'],
            'surat saat selesai' => ['selesai', 'issueSurat'],
            'sertifikat saat disetujui' => ['disetujui', 'complete'],
            'sertifikat saat verifikasi' => ['verifikasi', 'complete'],
        ];
    }

    #[DataProvider('disallowed')]
    public function test_disallowed_transition_throws_and_changes_nothing(string $status, string $action): void
    {
        $usulan = $this->usulan($status);

        try {
            match ($action) {
                'submit' => $this->sm->submit($usulan, $this->pegawai),
                'decide' => $this->sm->decide($usulan, $this->timJf, 'ms'),
                'resubmit' => $this->sm->resubmit($usulan, $this->pegawai),
                'issueSurat' => $this->sm->issueSurat($usulan, $this->timJf, 'surat/x.pdf'),
                'complete' => $this->sm->complete($usulan, $this->timJf, 'sertifikat/x.pdf'),
            };
            $this->fail("{$action} pada {$status} seharusnya ditolak");
        } catch (InvalidStatusTransition) {
        }

        $this->assertUnchanged($usulan, $status);
    }

    public function test_invalid_decision_value_is_rejected(): void
    {
        $usulan = $this->usulan('verifikasi');

        $this->expectException(InvalidStatusTransition::class);
        $this->sm->decide($usulan, $this->timJf, 'lulus');
    }

    // ---- otorisasi per role ----

    public function test_pegawai_cannot_decide(): void
    {
        $usulan = $this->usulan('verifikasi');

        $this->expectException(AuthorizationException::class);
        try {
            $this->sm->decide($usulan, $this->pegawai, 'ms');
        } finally {
            $this->assertUnchanged($usulan, 'verifikasi');
        }
    }

    public function test_only_owner_can_resubmit(): void
    {
        $usulan = $this->usulan('perbaikan');
        $other = User::factory()->pegawai()->create();

        foreach ([$other, $this->timJf] as $actor) {
            try {
                $this->sm->resubmit($usulan, $actor);
                $this->fail('Hanya pemilik yang boleh kirim ulang');
            } catch (AuthorizationException) {
            }
        }

        $this->assertUnchanged($usulan, 'perbaikan');
    }

    // ---- FR-MON-5: rollback Super Admin ----

    public function test_super_admin_rollback_with_reason_is_logged(): void
    {
        $usulan = $this->sm->rollback($this->usulan('ditetapkan'), $this->superAdmin, 'disetujui', 'Surat salah nomor');

        $this->assertSame('disetujui', $usulan->fresh()->status_key);
        $this->assertLogged($usulan, 'ditetapkan', 'disetujui', $this->superAdmin, 'Surat salah nomor');
    }

    public function test_tim_jf_cannot_rollback(): void
    {
        $usulan = $this->usulan('ditetapkan');

        $this->expectException(AuthorizationException::class);
        try {
            $this->sm->rollback($usulan, $this->timJf, 'disetujui', 'Alasan');
        } finally {
            $this->assertUnchanged($usulan, 'ditetapkan');
        }
    }

    public function test_rollback_without_reason_is_rejected(): void
    {
        $usulan = $this->usulan('selesai');

        $this->expectException(InvalidStatusTransition::class);
        try {
            $this->sm->rollback($usulan, $this->superAdmin, 'ditetapkan', '');
        } finally {
            $this->assertUnchanged($usulan, 'selesai');
        }
    }

    public function test_rollback_cannot_move_forward_or_sideways(): void
    {
        foreach ([['disetujui', 'selesai'], ['verifikasi', 'perbaikan'], ['ditetapkan', 'ditetapkan'], ['ditetapkan', 'tidak_ada']] as [$from, $to]) {
            $usulan = $this->usulan($from);
            try {
                $this->sm->rollback($usulan, $this->superAdmin, $to, 'Alasan');
                $this->fail("Rollback {$from} ke {$to} seharusnya ditolak");
            } catch (InvalidStatusTransition) {
            }
            $this->assertUnchanged($usulan, $from);
        }
    }

    public function test_full_lifecycle_writes_one_log_per_change(): void
    {
        $usulan = $this->usulan();

        $this->sm->submit($usulan, $this->pegawai);
        $this->sm->decide($usulan, $this->timJf, 'tms', 'Perbaiki SKP');
        $this->sm->resubmit($usulan, $this->pegawai);
        $this->sm->decide($usulan, $this->timJf, 'ms');
        $this->sm->issueSurat($usulan, $this->timJf, 'surat/a.pdf');
        $this->sm->complete($usulan, $this->timJf, 'sertifikat/a.pdf');

        $this->assertSame(
            [[null, 'verifikasi'], ['verifikasi', 'perbaikan'], ['perbaikan', 'verifikasi'], ['verifikasi', 'disetujui'], ['disetujui', 'ditetapkan'], ['ditetapkan', 'selesai']],
            $usulan->statusLogs()->get()->map(fn ($l) => [$l->from_status, $l->to_status])->all()
        );
        $this->assertSame('selesai', $usulan->fresh()->status_key);
    }
}
