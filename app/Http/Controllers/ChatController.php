<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chat Box Pegawai (FR-CHAT-1): satu thread dengan Tim Pembinaan JF, polling 5 detik.
 */
class ChatController extends Controller
{
    public function __construct(private ChatService $chat) {}

    /**
     * HTML untuk halaman, JSON untuk polling (?after={id}).
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $thread = $this->chat->threadFor($user);

        if ($thread) {
            Gate::authorize('view', $thread);
        }

        if ($request->wantsJson()) {
            $messages = $thread ? $this->chat->messagesAfter($thread, (int) $request->query('after', 0)) : collect();
            if ($thread) {
                $this->chat->markRead($thread, $user);
            }

            return response()->json([
                'messages' => $messages->map(fn (ChatMessage $m) => $m->toChatArray($user))->values(),
            ]);
        }

        $messages = $thread ? $thread->messages()->with('sender')->get() : collect();
        if ($thread) {
            $this->chat->markRead($thread, $user);
        }

        return response()->view('modules.chat', [
            'messages' => $messages,
            'viewer' => $user,
        ]);
    }

    public function store(Request $request): Response
    {
        $user = Auth::user();
        $validated = $request->validate($this->rules(), $this->messages());

        $thread = $this->chat->threadFor($user);
        if ($thread) {
            Gate::authorize('reply', $thread);
        }

        $message = $this->chat->send($user, $thread, $validated['body']);

        if ($request->wantsJson()) {
            return response()->json(['message' => $message->load('sender')->toChatArray($user)], 201);
        }

        return redirect()->route('chat.index');
    }

    private function rules(): array
    {
        return ['body' => ['required', 'string', 'max:'.ChatMessage::MAX_LENGTH]];
    }

    private function messages(): array
    {
        return [
            'body.required' => 'Pesan tidak boleh kosong.',
            'body.max' => 'Pesan maksimal '.ChatMessage::MAX_LENGTH.' karakter.',
        ];
    }
}
