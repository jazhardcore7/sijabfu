<?php

namespace Tests\Feature\Phase6;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

/**
 * Chat Box dan Messenger (PRD Revisi 5.8): FR-CHAT-1 s.d. FR-CHAT-4, FR-RBAC-2 (pesan).
 */
class ChatTest extends TestCase
{
    use RefreshDatabase;

    private User $pegawai;
    private User $timJf;
    private User $timJf2;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pegawai = User::factory()->pegawai()->create(['name' => 'Pegawai Satu']);
        $this->timJf = User::factory()->timJf()->create();
        $this->timJf2 = User::factory()->timJf()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
    }

    private function send(User $user, string $url, string $body)
    {
        return $this->actingAs($user)->postJson($url, ['body' => $body]);
    }

    // FR-CHAT-1
    public function test_pegawai_message_creates_thread_and_appears_in_tim_jf_inbox_on_next_poll(): void
    {
        $this->actingAs($this->pegawai)->get('/chat')->assertOk()->assertSee('Belum ada pesan');

        $this->send($this->pegawai, '/chat', 'Halo Tim, berkas saya bagaimana?')->assertCreated()
            ->assertJsonPath('message.mine', true);

        $thread = ChatThread::sole();
        $this->assertSame($this->pegawai->id, (int) $thread->pegawai_id);
        $this->assertNotNull($thread->last_message_at);

        // satu siklus polling Tim JF: daftar thread dan isi thread
        $inbox = $this->actingAs($this->timJf)->getJson('/messenger')->assertOk();
        $inbox->assertJsonPath('threads.0.id', $thread->id)
            ->assertJsonPath('threads.0.pegawai', 'Pegawai Satu')
            ->assertJsonPath('threads.0.unread', 1)
            ->assertJsonPath('threads.0.last_body', 'Halo Tim, berkas saya bagaimana?');

        $this->actingAs($this->timJf)->getJson("/messenger/{$thread->id}?after=0")
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'Halo Tim, berkas saya bagaimana?')
            ->assertJsonPath('messages.0.mine', false);
    }

    // FR-CHAT-2
    public function test_staff_reply_appears_for_pegawai_within_one_poll(): void
    {
        $this->send($this->pegawai, '/chat', 'Pertanyaan');
        $thread = ChatThread::sole();
        $lastId = ChatMessage::max('id');

        $this->send($this->timJf, "/messenger/{$thread->id}", 'Jawaban Tim JF')->assertCreated();

        $poll = $this->actingAs($this->pegawai)->getJson("/chat?after={$lastId}")->assertOk();
        $poll->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.body', 'Jawaban Tim JF')
            ->assertJsonPath('messages.0.sender_level', 'Tim Pembinaan JF');
    }

    // FR-CHAT-1: badge belum dibaca, lalu dibaca saat membuka
    public function test_pegawai_unread_badge_and_mark_read(): void
    {
        $this->send($this->pegawai, '/chat', 'Halo');
        $thread = ChatThread::sole();
        $this->send($this->timJf, "/messenger/{$thread->id}", 'Balasan 1');
        $this->send($this->timJf, "/messenger/{$thread->id}", 'Balasan 2');

        $this->actingAs($this->pegawai)->get('/regulasi')->assertSee('data-chat-unread="2"', false);

        $this->actingAs($this->pegawai)->get('/chat')->assertOk();

        $this->actingAs($this->pegawai)->get('/regulasi')->assertDontSee('data-chat-unread', false);
        $this->assertSame(0, ChatMessage::whereNull('read_at')->where('sender_id', $this->timJf->id)->count());
    }

    // FR-CHAT-2: inbox bersama, urut pesan terbaru, unread hilang untuk semua staf
    public function test_shared_inbox_sorted_by_latest_message(): void
    {
        $p2 = User::factory()->pegawai()->create(['name' => 'Pegawai Dua']);

        Date::setTestNow(now()->subMinutes(10));
        $this->send($this->pegawai, '/chat', 'Pesan lama');
        Date::setTestNow(now()->addMinutes(5));
        $this->send($p2, '/chat', 'Pesan baru');
        Date::setTestNow();

        foreach ([$this->timJf, $this->timJf2] as $staff) {
            $this->actingAs($staff)->getJson('/messenger')
                ->assertJsonPath('threads.0.pegawai', 'Pegawai Dua')
                ->assertJsonPath('threads.1.pegawai', 'Pegawai Satu');
        }

        $this->actingAs($this->timJf)->get('/regulasi')->assertSee('data-chat-unread="2"', false);

        $thread1 = ChatThread::where('pegawai_id', $this->pegawai->id)->sole();
        $this->actingAs($this->timJf)->get("/messenger/{$thread1->id}")->assertOk()->assertSee('Pesan lama');

        // terbaca untuk seluruh tim
        $this->actingAs($this->timJf2)->getJson('/messenger')
            ->assertJsonPath('threads.1.unread', 0)
            ->assertJsonPath('threads.0.unread', 1);
        $this->actingAs($this->timJf2)->get('/regulasi')->assertSee('data-chat-unread="1"', false);

        // balasan memindahkan thread ke atas
        $this->send($this->timJf2, "/messenger/{$thread1->id}", 'Balasan');
        $this->actingAs($this->timJf)->getJson('/messenger')->assertJsonPath('threads.0.pegawai', 'Pegawai Satu');
    }

    // FR-CHAT-3
    public function test_super_admin_can_view_all_threads_and_reply(): void
    {
        $this->send($this->pegawai, '/chat', 'Halo');
        $thread = ChatThread::sole();

        $this->actingAs($this->superAdmin)->get('/messenger')->assertOk()->assertSee('Pegawai Satu');
        $this->actingAs($this->superAdmin)->get("/messenger/{$thread->id}")->assertOk()->assertSee('Halo');
        $this->send($this->superAdmin, "/messenger/{$thread->id}", 'Balasan Super Admin')->assertCreated();

        $this->actingAs($this->pegawai)->getJson('/chat?after=0')
            ->assertJsonPath('messages.1.body', 'Balasan Super Admin')
            ->assertJsonPath('messages.1.sender_level', 'Super Admin');
    }

    // FR-CHAT-4
    public function test_message_over_2000_characters_is_rejected(): void
    {
        $this->send($this->pegawai, '/chat', str_repeat('a', 2001))->assertStatus(422)->assertJsonValidationErrors('body');
        $this->send($this->pegawai, '/chat', '')->assertStatus(422);
        $this->assertSame(0, ChatMessage::count());

        $this->send($this->pegawai, '/chat', str_repeat('a', 2000))->assertCreated();
    }

    // FR-CHAT-4
    public function test_html_is_shown_as_text(): void
    {
        $payload = '<script>alert("x")</script><b>tebal</b>';
        $this->send($this->pegawai, '/chat', $payload);
        $thread = ChatThread::sole();

        $page = $this->actingAs($this->timJf)->get("/messenger/{$thread->id}")->getContent();
        $this->assertStringNotContainsString('<script>alert("x")</script>', $page);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;&lt;b&gt;tebal&lt;/b&gt;', $page);

        $this->actingAs($this->pegawai)->get('/chat')->assertDontSee('<b>tebal</b>', false);

        // JSON mengirim teks apa adanya; klien merender dengan textContent
        $this->actingAs($this->timJf)->getJson("/messenger/{$thread->id}?after=0")->assertJsonPath('messages.0.body', $payload);
        $script = file_get_contents(resource_path('views/chat/_conversation.blade.php'));
        $this->assertStringContainsString('body.textContent = msg.body', $script);
        $this->assertStringNotContainsString('innerHTML', $script);
    }

    // FR-CHAT-4
    public function test_chat_is_throttled_at_30_per_minute(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $this->send($this->pegawai, '/chat', "Pesan {$i}")->assertCreated();
        }

        $this->send($this->pegawai, '/chat', 'Pesan 31')->assertStatus(429);
        $this->assertSame(30, ChatMessage::count());
    }

    // FR-RBAC-2 (pesan)
    public function test_access_control_for_threads(): void
    {
        $other = User::factory()->pegawai()->create();
        $this->send($this->pegawai, '/chat', 'Rahasia pegawai satu');
        $thread = ChatThread::sole();

        // pegawai lain hanya melihat thread kosong miliknya
        $this->actingAs($other)->get('/chat')->assertOk()->assertDontSee('Rahasia pegawai satu');
        $this->actingAs($other)->getJson('/chat?after=0')->assertJsonCount(0, 'messages');

        // pegawai tidak boleh membuka messenger
        $this->actingAs($other)->get('/messenger')->assertForbidden();
        $this->actingAs($other)->get("/messenger/{$thread->id}")->assertForbidden();
        $this->actingAs($this->pegawai)->get("/messenger/{$thread->id}")->assertForbidden();
        $this->actingAs($other)->postJson("/messenger/{$thread->id}", ['body' => 'Menyusup'])->assertForbidden();

        // staf tidak memakai Chat Box pegawai
        $this->actingAs($this->timJf)->postJson('/chat', ['body' => 'x'])->assertForbidden();

        $this->assertSame(1, ChatMessage::count());
        $this->assertSame(1, ChatThread::count());
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/chat')->assertRedirect(route('login'));
        $this->get('/messenger')->assertRedirect(route('login'));
        $this->post('/chat', ['body' => 'x'])->assertRedirect(route('login'));
        $this->assertSame(0, ChatMessage::count());
    }

    public function test_non_json_post_redirects_back_to_page(): void
    {
        $this->actingAs($this->pegawai)->post('/chat', ['body' => 'Lewat form biasa'])->assertRedirect(route('chat.index'));
        $thread = ChatThread::sole();
        $this->actingAs($this->timJf)->post("/messenger/{$thread->id}", ['body' => 'Balas form'])->assertRedirect(route('messenger.show', $thread));
    }
}
