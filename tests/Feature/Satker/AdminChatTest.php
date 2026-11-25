<?php

namespace Tests\Feature\Satker;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * FR-SATKER-17: Admin Satuan Kerja Provinsi punya thread sendiri dengan Tim Pembinaan; Messenger menampilkan level.
 */
class AdminChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_chats_with_tim_and_messenger_shows_level(): void
    {
        $admin = User::factory()->adminSatker('32')->create(['name' => 'Admin Chat Uji']);
        $timJf = User::factory()->timJf()->create();

        $this->actingAs($admin)->get('/dashboard')->assertSee('data-menu-route="chat.index"', false);
        $this->actingAs($admin)->get('/chat')->assertOk();
        $this->actingAs($admin)->postJson('/chat', ['body' => 'Halo Tim, pertanyaan usulan'])->assertCreated();

        $thread = ChatThread::sole();
        $this->assertSame($admin->id, (int) $thread->pegawai_id);

        $this->actingAs($timJf)->get('/messenger')->assertOk()
            ->assertSee('Admin Chat Uji')
            ->assertSee('data-level="admin_satker"', false)
            ->assertSee('Admin Satuan Kerja Provinsi');
        $this->actingAs($timJf)->getJson('/messenger')->assertJsonPath('threads.0.level', 'Admin Satuan Kerja Provinsi');

        // Balasan Tim JF: badge belum dibaca untuk Admin, terbaca setelah membuka Chat Box
        $this->actingAs($timJf)->postJson("/messenger/{$thread->id}", ['body' => 'Silakan, kami bantu'])->assertCreated();
        $this->actingAs($admin)->get('/regulasi')->assertSee('data-chat-unread="1"', false);
        $this->actingAs($admin)->getJson('/chat?after=0')->assertOk()->assertJsonCount(2, 'messages');
        $this->assertSame(0, ChatMessage::whereNull('read_at')->where('sender_id', $timJf->id)->count());
        $this->assertSame(1, $admin->fresh()->notifications()->count());
    }

    public function test_admin_cannot_open_other_threads_or_messenger(): void
    {
        $admin = User::factory()->adminSatker('32')->create();
        $pegawai = User::factory()->pegawai()->create();
        $thread = ChatThread::create(['pegawai_id' => $pegawai->id]);

        $this->assertFalse(Gate::forUser($admin)->allows('view', $thread));
        $this->actingAs($admin)->get('/messenger')->assertForbidden();
        $this->actingAs($admin)->get("/messenger/{$thread->id}")->assertForbidden();
        $this->actingAs($admin)->post("/messenger/{$thread->id}", ['body' => 'x'])->assertForbidden();
    }

    public function test_tim_kerja_profile_offers_chat_to_admin(): void
    {
        $admin = User::factory()->adminSatker('32')->create();
        $anggota = User::factory()->timJf()->create();

        $this->actingAs($admin)->get("/tim-kerja/{$anggota->id}")->assertOk()->assertSee('data-action="chat-tim"', false);
    }
}
