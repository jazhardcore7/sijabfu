{{--
    Percakapan chat dengan polling 5 detik (FR-CHAT-1, FR-CHAT-2, FR-CHAT-4).
    Variabel: $messages, $viewer, $pollUrl, $postUrl, $emptyText
    Isi pesan selalu dirender sebagai teks (Blade escape / textContent).
--}}
@push('styles')
<style>
    .chat-box { display: flex; flex-direction: column; height: 560px; background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 18px; overflow: hidden; }
    .chat-messages { flex: 1; overflow-y: auto; padding: 18px; display: flex; flex-direction: column; gap: 10px; background: var(--c-surface-2); }
    .chat-msg { max-width: 75%; padding: 10px 14px; border-radius: 14px; font-size: 13.5px; line-height: 1.5; }
    .chat-msg.theirs { align-self: flex-start; background: var(--c-surface); border: 1px solid var(--c-border); color: var(--c-text); }
    .chat-msg.mine { align-self: flex-end; background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); color: #FFFFFF; }
    .chat-meta { font-size: 11px; opacity: 0.75; margin-bottom: 3px; }
    .chat-body { white-space: pre-wrap; word-break: break-word; }
    .chat-empty { margin: auto; color: var(--c-text-muted); font-size: 13px; text-align: center; }
    .chat-form { display: flex; gap: 10px; padding: 12px; border-top: 1px solid var(--c-border); background: var(--c-surface); }
    .chat-input {
        flex: 1; resize: none; height: 46px; border: 1.5px solid var(--c-border-strong); border-radius: 12px;
        padding: 10px 12px; font-family: inherit; font-size: 13.5px; color: var(--c-text); background: var(--c-surface);
    }
    .chat-send { border: none; border-radius: 12px; padding: 0 20px; font-weight: 700; cursor: pointer; color: #FFFFFF; background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); }
    .chat-error { color: #DC2626; font-size: 12px; padding: 0 12px 8px; background: var(--c-surface); }
    .chat-counter { font-size: 11px; color: var(--c-text-faint); padding: 0 12px 8px; text-align: right; background: var(--c-surface); }
</style>
@endpush

<div class="chat-box" data-section="chat-box">
    <div class="chat-messages" id="chatMessages" data-poll-url="{{ $pollUrl }}">
        @forelse($messages as $m)
        <div class="chat-msg {{ (int) $m->sender_id === (int) $viewer->id ? 'mine' : 'theirs' }}" data-message-id="{{ $m->id }}">
            <div class="chat-meta">{{ $m->sender?->name }} · {{ $m->sender?->levelLabel() }} · {{ $m->created_at->format('d M Y H:i') }}</div>
            <div class="chat-body">{{ $m->body }}</div>
        </div>
        @empty
        <div class="chat-empty" id="chatEmpty">{{ $emptyText }}</div>
        @endforelse
    </div>
    @error('body')<div class="chat-error">{{ $message }}</div>@enderror
    <form action="{{ $postUrl }}" method="POST" class="chat-form" id="chatForm">
        @csrf
        <textarea name="body" class="chat-input" id="chatInput" maxlength="{{ \App\Models\ChatMessage::MAX_LENGTH }}" placeholder="Tulis pesan..." required>{{ old('body') }}</textarea>
        <button type="submit" class="chat-send">Kirim</button>
    </form>
    <div class="chat-counter"><span id="chatCount">0</span>/{{ \App\Models\ChatMessage::MAX_LENGTH }}</div>
</div>

@push('scripts')
<script>
(function () {
    const box = document.getElementById('chatMessages');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const counter = document.getElementById('chatCount');
    const pollUrl = box.dataset.pollUrl;
    const token = form.querySelector('input[name=_token]').value;

    function lastId() {
        const items = box.querySelectorAll('[data-message-id]');
        return items.length ? parseInt(items[items.length - 1].dataset.messageId, 10) : 0;
    }

    function render(msg) {
        if (box.querySelector('[data-message-id="' + msg.id + '"]')) return;
        const empty = document.getElementById('chatEmpty');
        if (empty) empty.remove();

        const wrap = document.createElement('div');
        wrap.className = 'chat-msg ' + (msg.mine ? 'mine' : 'theirs');
        wrap.dataset.messageId = msg.id;
        const meta = document.createElement('div');
        meta.className = 'chat-meta';
        meta.textContent = msg.sender + ' · ' + msg.sender_level + ' · ' + msg.time;
        const body = document.createElement('div');
        body.className = 'chat-body';
        body.textContent = msg.body;
        wrap.appendChild(meta);
        wrap.appendChild(body);
        box.appendChild(wrap);
        box.scrollTop = box.scrollHeight;
    }

    async function poll() {
        try {
            const url = pollUrl + (pollUrl.includes('?') ? '&' : '?') + 'after=' + lastId();
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            (data.messages || []).forEach(render);
        } catch (e) { /* jaringan putus: coba lagi pada siklus berikutnya */ }
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
                body: JSON.stringify({ body: text })
            });
            if (res.ok) {
                const data = await res.json();
                render(data.message);
                input.value = '';
                counter.textContent = '0';
            } else if (res.status === 429) {
                alert('Terlalu banyak pesan. Coba lagi sebentar lagi.');
            } else {
                form.submit();
            }
        } catch (err) {
            form.submit();
        }
    });

    input.addEventListener('input', () => { counter.textContent = input.value.length; });
    box.scrollTop = box.scrollHeight;
    setInterval(poll, 5000);
})();
</script>
@endpush
