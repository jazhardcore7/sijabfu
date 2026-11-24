<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Messenger Tim Pembinaan JF (FR-CHAT-2) dan Super Admin (FR-CHAT-3): inbox bersama.
 */
class MessengerController extends Controller
{
    public function __construct(private ChatService $chat)
    {
    }

    public function index(Request $request): Response
    {
        if ($request->wantsJson()) {
            return response()->json(['threads' => $this->threadsJson()]);
        }

        return response()->view('messenger.index', [
            'threads' => $this->chat->inbox(),
            'active' => null,
            'messages' => collect(),
            'viewer' => Auth::user(),
        ]);
    }

    public function show(Request $request, ChatThread $thread): Response
    {
        Gate::authorize('view', $thread);
        $user = Auth::user();

        if ($request->wantsJson()) {
            $messages = $this->chat->messagesAfter($thread, (int) $request->query('after', 0));
            $this->chat->markRead($thread, $user);

            return response()->json([
                'messages' => $messages->map(fn (ChatMessage $m) => $m->toChatArray($user))->values(),
            ]);
        }

        $messages = $thread->messages()->with('sender')->get();
        $this->chat->markRead($thread, $user);

        return response()->view('messenger.index', [
            'threads' => $this->chat->inbox(),
            'active' => $thread->load('pegawai'),
            'messages' => $messages,
            'viewer' => $user,
        ]);
    }

    public function reply(Request $request, ChatThread $thread): Response
    {
        Gate::authorize('reply', $thread);
        $user = Auth::user();

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:' . ChatMessage::MAX_LENGTH],
        ], [
            'body.required' => 'Pesan tidak boleh kosong.',
            'body.max' => 'Pesan maksimal ' . ChatMessage::MAX_LENGTH . ' karakter.',
        ]);

        $message = $this->chat->send($user, $thread, $validated['body']);

        if ($request->wantsJson()) {
            return response()->json(['message' => $message->load('sender')->toChatArray($user)], 201);
        }

        return redirect()->route('messenger.show', $thread);
    }

    private function threadsJson(): array
    {
        return $this->chat->inbox()->map(fn (ChatThread $t) => [
            'id' => $t->id,
            'pegawai' => $t->pegawai?->name,
            'nip' => $t->pegawai?->nip,
            'last_body' => Str::limit((string) $t->latestMessage?->body, 80),
            'last_at' => $t->last_message_at?->format('d M Y H:i'),
            'unread' => (int) $t->unread_count,
            'url' => route('messenger.show', $t),
        ])->values()->all();
    }
}
