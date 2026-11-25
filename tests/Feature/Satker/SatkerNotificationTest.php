<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use App\Services\UsulanStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-16: notifikasi perubahan status untuk pengaju dan pegawai yang diusulkan, sekali per orang,
 * dengan tautan yang berlaku untuk masing-masing penerima.
 */
class SatkerNotificationTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    /**
     * Notifikasi user untuk satu event (created_at bisa sama dalam satu detik, jadi dicari per event).
     */
    private function notifFor(User $user, string $event = 'decide'): ?array
    {
        return $user->fresh()->notifications()->get()->first(fn ($n) => ($n->data['event'] ?? null) === $event)?->data;
    }

    public function test_admin_submitted_tms_notifies_admin_and_pegawai_once_each(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->provinsi('32')->create(['name' => 'Pegawai Notif']);
        $timJf = User::factory()->timJf()->create();
        $usulan = $this->usulanAt($pegawai, FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $admin->id]);

        app(UsulanStateMachine::class)->decide($usulan, $timJf, 'tms', 'Perbaiki SKP');

        $this->assertSame(2, DatabaseNotification::count());
        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(1, $pegawai->notifications()->count());

        $forAdmin = $this->notifFor($admin);
        $this->assertSame(route('satker.pengajuan.edit', $usulan->id), $forAdmin['url']);
        $this->assertStringContainsString('Pegawai Notif', $forAdmin['pesan']);
        $this->assertStringContainsString('Perbaiki SKP', $forAdmin['pesan']);

        $forPegawai = $this->notifFor($pegawai);
        $this->assertSame(route('pelacakan-status'), $forPegawai['url']);
        $this->assertStringContainsString('Admin Satuan Kerja akan memperbaiki', $forPegawai['pesan']);

        // Tautan masing-masing dapat dibuka oleh penerimanya
        $this->actingAs($admin)->get($forAdmin['url'])->assertOk();
        $this->actingAs($pegawai)->get($forPegawai['url'])->assertOk();
    }

    public function test_later_events_link_admin_to_tracker_detail(): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->provinsi('32')->create();
        $timJf = User::factory()->timJf()->create();
        $usulan = $this->usulanAt($pegawai, FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $admin->id]);
        $sm = app(UsulanStateMachine::class);

        $sm->decide($usulan, $timJf, 'ms');
        $this->assertSame(route('satker.pelacakan.show', $usulan->id), $this->notifFor($admin)['url']);

        $sm->issueSurat($usulan->fresh(), $timJf, 'usulan/surat.pdf');
        $this->assertSame('Surat usulan telah terbit', $this->notifFor($admin, 'issueSurat')['judul']);
        $this->assertSame(route('satker.pelacakan.show', $usulan->id), $this->notifFor($admin, 'issueSurat')['url']);
        $this->assertSame(route('pelacakan-status'), $this->notifFor($pegawai, 'issueSurat')['url']);

        $sm->complete($usulan->fresh(), $timJf, 'usulan/sertifikat.pdf');
        $this->assertSame(3, $admin->notifications()->count());
        $this->assertSame(3, $pegawai->notifications()->count());
    }

    public function test_self_submitted_usulan_creates_exactly_one_notification(): void
    {
        Storage::fake('local');
        $pegawai = User::factory()->pegawai()->pusat()->create();
        $timJf = User::factory()->timJf()->create();
        $usulan = $this->usulanAt($pegawai, FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $pegawai->id]);

        app(UsulanStateMachine::class)->decide($usulan, $timJf, 'tms', 'Catatan');

        $this->assertSame(1, DatabaseNotification::count());
        $this->assertSame(route('pengajuan'), $this->notifFor($pegawai)['url']);
        $this->assertSame('Berkas usulan Anda dinyatakan TMS. Silakan perbaiki dan kirim ulang. Catatan: Catatan', $this->notifFor($pegawai)['pesan']);
    }
}
