<?php

namespace Tests\Feature\Phase7;

use App\Exceptions\InvalidStatusTransition;
use App\Models\FormUsulan;
use App\Models\User;
use App\Notifications\PesanChatBaru;
use App\Notifications\StatusUsulanBerubah;
use App\Notifications\UsulanDikirim;
use App\Services\UsulanStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-NOT-1: notifikasi database per event untuk penerima yang tepat; lonceng dan tandai dibaca.
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $pegawai;
    private User $timJf1;
    private User $timJf2;
    private User $timJfNonaktif;
    private User $superAdmin;
    private UsulanStateMachine $sm;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->pegawai = User::factory()->pegawai()->create(['name' => 'Pegawai Notif']);
        $this->timJf1 = User::factory()->timJf()->create();
        $this->timJf2 = User::factory()->timJf()->create();
        $this->timJfNonaktif = User::factory()->timJf()->create(['is_active' => false]);
        $this->superAdmin = User::factory()->superAdmin()->create();
        $this->sm = app(UsulanStateMachine::class);
    }

    /** jumlah notifikasi per user id untuk tipe tertentu */
    private function recipients(string $type): array
    {
        return DB::table('notifications')->where('type', $type)
            ->selectRaw('notifiable_id, count(*) as n')->groupBy('notifiable_id')
            ->pluck('n', 'notifiable_id')->map(fn ($n) => (int) $n)->sortKeys()->all();
    }

    private function expectOnly(string $type, array $users): void
    {
        $expected = collect($users)->mapWithKeys(fn (User $u) => [$u->id => 1])->sortKeys()->all();
        $this->assertSame($expected, $this->recipients($type), $type);
    }

    public function test_submit_notifies_each_active_tim_jf_once(): void
    {
        $this->actingAs($this->pegawai)->post('/pengajuan', $this->dataUsulan(['aksi' => 'kirim', 'berkas' => $this->allBerkas()]));

        $this->expectOnly(UsulanDikirim::class, [$this->timJf1, $this->timJf2]);
        $data = $this->timJf1->notifications()->sole()->data;
        $this->assertSame('Usulan baru masuk', $data['judul']);
        $this->assertSame(route('verifikasi.show', FormUsulan::sole()->id), $data['url']);
    }

    public function test_resubmit_notifies_tim_jf(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'perbaikan');

        $this->sm->resubmit($usulan, $this->pegawai);

        $this->expectOnly(UsulanDikirim::class, [$this->timJf1, $this->timJf2]);
        $this->assertSame('Usulan dikirim ulang', $this->timJf2->notifications()->sole()->data['judul']);
    }

    public function test_status_changes_notify_the_owner_once_each(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'verifikasi');

        $this->sm->decide($usulan, $this->timJf1, 'tms', 'Perbaiki SKP');
        $this->expectOnly(StatusUsulanBerubah::class, [$this->pegawai]);
        $this->assertSame('Usulan perlu perbaikan (TMS)', $this->pegawai->notifications()->latest()->first()->data['judul']);

        $this->sm->resubmit($usulan, $this->pegawai);
        $this->sm->decide($usulan, $this->timJf1, 'ms');
        $this->sm->issueSurat($usulan, $this->timJf1, 'usulan/s.pdf');
        $this->sm->complete($usulan, $this->timJf1, 'usulan/c.pdf');
        $this->sm->rollback($usulan, $this->superAdmin, 'ditetapkan', 'Sertifikat salah');

        $judul = $this->pegawai->notifications()->orderBy('created_at')->orderBy('id')->get()->pluck('data.judul')->all();
        $this->assertEqualsCanonicalizing([
            'Usulan perlu perbaikan (TMS)',
            'Usulan disetujui (MS)',
            'Surat usulan telah terbit',
            'Sertifikat UKOM telah diunggah',
            'Status usulan diubah',
        ], $judul);
        $this->assertSame([$this->pegawai->id => 5], $this->recipients(StatusUsulanBerubah::class), 'tidak ada staf yang menerima notifikasi status');
    }

    public function test_rejected_transition_creates_no_notification(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'disetujui');

        try {
            $this->sm->decide($usulan, $this->timJf1, 'ms');
        } catch (InvalidStatusTransition) {
        }

        $this->assertSame(0, DB::table('notifications')->count());
    }

    public function test_chat_message_notifies_the_recipient_side(): void
    {
        $this->actingAs($this->pegawai)->postJson('/chat', ['body' => 'Halo tim'])->assertCreated();
        $this->expectOnly(PesanChatBaru::class, [$this->timJf1, $this->timJf2]);

        DB::table('notifications')->delete();
        $thread = \App\Models\ChatThread::sole();
        $this->actingAs($this->timJf1)->postJson("/messenger/{$thread->id}", ['body' => 'Halo juga'])->assertCreated();
        $this->expectOnly(PesanChatBaru::class, [$this->pegawai]);
        $this->assertSame(route('chat.index'), $this->pegawai->notifications()->sole()->data['url']);
    }

    public function test_bell_shows_unread_count_and_latest_items(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'verifikasi');
        $this->sm->decide($usulan, $this->timJf1, 'ms');

        $this->actingAs($this->pegawai)->get('/regulasi')
            ->assertSee('<span class="notif-count" data-count="1">1</span>', false)
            ->assertSee('Usulan disetujui (MS)')
            ->assertSee('data-action="baca-semua"', false);
    }

    public function test_opening_a_notification_marks_it_read_and_redirects(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'verifikasi');
        $this->sm->decide($usulan, $this->timJf1, 'ms');
        $notif = $this->pegawai->notifications()->sole();

        $this->actingAs($this->pegawai)->get("/notifikasi/{$notif->id}")->assertRedirect(route('pelacakan-status'));

        $this->assertNotNull($notif->fresh()->read_at);
        $this->actingAs($this->pegawai)->get('/regulasi')->assertSee('data-count="0"', false);
    }

    public function test_read_all_marks_everything_read(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'verifikasi');
        $this->sm->decide($usulan, $this->timJf1, 'ms');
        $this->sm->issueSurat($usulan, $this->timJf1, 'usulan/s.pdf');

        $this->actingAs($this->pegawai)->post('/notifikasi/baca-semua')->assertRedirect();

        $this->assertSame(0, $this->pegawai->unreadNotifications()->count());
        $this->assertSame(2, $this->pegawai->notifications()->count());
    }

    public function test_cannot_open_another_users_notification(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'verifikasi');
        $this->sm->decide($usulan, $this->timJf1, 'ms');
        $notif = $this->pegawai->notifications()->sole();

        $this->actingAs(User::factory()->pegawai()->create())->get("/notifikasi/{$notif->id}")->assertNotFound();
        $this->actingAs($this->timJf1)->get("/notifikasi/{$notif->id}")->assertNotFound();
        $this->assertNull($notif->fresh()->read_at);
    }

    public function test_guest_is_redirected_from_notifications(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'verifikasi');
        $this->sm->decide($usulan, $this->timJf1, 'ms');
        $notif = $this->pegawai->notifications()->sole();

        $this->get("/notifikasi/{$notif->id}")->assertRedirect(route('login'));
        $this->post('/notifikasi/baca-semua')->assertRedirect(route('login'));
        $this->assertNull($notif->fresh()->read_at);
    }
}
