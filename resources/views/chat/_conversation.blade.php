{{--
    Percakapan chat dengan polling 5 detik (FR-CHAT-1, FR-CHAT-2, FR-CHAT-4).
    Variabel: $messages, $viewer, $pollUrl, $postUrl, $emptyText
    Isi pesan selalu dirender sebagai teks (Blade escape / textContent).
--}}
{{-- Gaya: public/css/sijabfu.css (bagian "Chat"). --}}
<div class="chat-box" data-section="chat-box">
    <div class="chat-messages" id="chatMessages" data-poll-url="{{ $pollUrl }}" role="log" aria-live="polite" aria-label="Pesan" tabindex="0">
        @forelse($messages as $m)
        <div class="chat-msg {{ (int) $m->sender_id === (int) $viewer->id ? 'mine' : 'theirs' }}" data-message-id="{{ $m->id }}">
            <div class="chat-meta">{{ $m->sender?->name }} · {{ $m->sender?->levelLabel() }} · {{ $m->created_at->format('d M Y H:i') }}</div>
            <div class="chat-body">{{ $m->body }}</div>
        </div>
        @empty
        <div class="chat-empty" id="chatEmpty">{{ $emptyText }}</div>
        @endforelse
    </div>
    <div class="chat-error" id="chatError" role="alert" @unless($errors->has('body')) hidden @endunless>@error('body'){{ $message }}@enderror</div>
    <form action="{{ $postUrl }}" method="POST" class="chat-form" id="chatForm">
        @csrf
        <label for="chatInput" class="sr-only">Tulis pesan</label>
        <textarea name="body" class="chat-input" id="chatInput" aria-describedby="chatError" maxlength="{{ \App\Models\ChatMessage::MAX_LENGTH }}" placeholder="Tulis pesan..." required>{{ old('body') }}</textarea>
        <button type="submit" class="btn btn--primary chat-send" id="chatSend"><x-icon name="send" /> Kirim</button>
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
    const sendBtn = document.getElementById('chatSend');
    const errorBox = document.getElementById('chatError');

    function showError(text) {
        errorBox.textContent = text;
        errorBox.hidden = !text;
    }
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
        if (!text || sendBtn.getAttribute('aria-busy') === 'true') return;
        showError('');
        sendBtn.setAttribute('aria-busy', 'true');
        sendBtn.setAttribute('aria-disabled', 'true');
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
                showError('Terlalu banyak pesan. Coba lagi sebentar lagi.');
            } else {
                form.submit();
            }
        } catch (err) {
            form.submit();
        } finally {
            sendBtn.removeAttribute('aria-busy');
            sendBtn.removeAttribute('aria-disabled');
        }
    });

    input.addEventListener('input', () => { counter.textContent = input.value.length; });
    box.scrollTop = box.scrollHeight;
    setInterval(poll, 5000);
})();
</script>
@endpush
