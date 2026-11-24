@extends('layouts.app')

@section('title', 'Messenger')
@section('breadcrumb', 'Chat Box (Messenger)')

@push('styles')
<style>
    .messenger { display: grid; grid-template-columns: 320px 1fr; gap: 18px; align-items: start; }
    .thread-list { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 18px; overflow: hidden; }
    .thread-list-header { padding: 14px 18px; font-size: 14px; font-weight: 700; color: var(--c-text); background: var(--c-surface-2); border-bottom: 1px solid var(--c-border); }
    .thread-item { display: block; padding: 12px 18px; border-bottom: 1px solid var(--c-border-soft); text-decoration: none; color: var(--c-text); }
    .thread-item.active { background: var(--c-surface-3); }
    .thread-top { display: flex; justify-content: space-between; gap: 8px; align-items: center; }
    .thread-name { font-weight: 700; font-size: 13.5px; }
    .thread-snippet { font-size: 12px; color: var(--c-text-muted); margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .thread-time { font-size: 11px; color: var(--c-text-faint); }
    .unread-badge { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px; background: #EF4444; color: #FFFFFF; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
    .thread-empty, .messenger-empty { padding: 24px 18px; color: var(--c-text-muted); font-size: 13px; text-align: center; }
    .messenger-empty { background: var(--c-surface); border: 2px dashed var(--c-border-strong); border-radius: 18px; padding: 60px 20px; }
    .active-header { font-size: 15px; font-weight: 800; color: var(--c-text); margin-bottom: 10px; }
    @media (max-width: 900px) { .messenger { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">💬 Messenger</h1>
    <p class="page-header-desc">Daftar pesan dari pegawai untuk berkomunikasi. Inbox dipakai bersama seluruh Tim Pembinaan JF.</p>
    <div class="page-header-divider"></div>
</div>

<div class="messenger">
    <div class="thread-list">
        <div class="thread-list-header">📥 Daftar Pesan dari Pegawai</div>
        <div id="threadList" data-poll-url="{{ route('messenger.index') }}" data-active="{{ $active?->id }}">
            @forelse($threads as $t)
            <a href="{{ route('messenger.show', $t) }}" class="thread-item {{ $active && $active->id === $t->id ? 'active' : '' }}" data-thread="{{ $t->id }}">
                <div class="thread-top">
                    <span class="thread-name">{{ $t->pegawai?->name }}</span>
                    @if($t->unread_count > 0)
                    <span class="unread-badge" data-unread="{{ $t->unread_count }}">{{ $t->unread_count }}</span>
                    @endif
                </div>
                <div class="thread-snippet">{{ \Illuminate\Support\Str::limit((string) $t->latestMessage?->body, 80) }}</div>
                <div class="thread-time">{{ $t->last_message_at?->format('d M Y H:i') }}</div>
            </a>
            @empty
            <div class="thread-empty">Belum ada pesan dari pegawai.</div>
            @endforelse
        </div>
    </div>

    <div>
        @if($active)
        <div class="active-header" data-active-thread="{{ $active->id }}">
            {{ $active->pegawai?->name }} <span style="font-weight:500; color:var(--c-text-muted); font-size:13px;">NIP {{ $active->pegawai?->nip }}</span>
        </div>
        @include('chat._conversation', [
            'messages' => $messages,
            'viewer' => $viewer,
            'pollUrl' => route('messenger.show', $active),
            'postUrl' => route('messenger.reply', $active),
            'emptyText' => 'Belum ada pesan.',
        ])
        @else
        <div class="messenger-empty">Pilih pesan dari daftar untuk membuka percakapan.</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Muat ulang daftar thread setiap 5 detik (FR-CHAT-2); teks dirender dengan textContent.
    const list = document.getElementById('threadList');
    const activeId = list.dataset.active;

    function item(t) {
        const a = document.createElement('a');
        a.href = t.url;
        a.className = 'thread-item' + (String(t.id) === activeId ? ' active' : '');
        a.dataset.thread = t.id;
        const top = document.createElement('div');
        top.className = 'thread-top';
        const name = document.createElement('span');
        name.className = 'thread-name';
        name.textContent = t.pegawai;
        top.appendChild(name);
        if (t.unread > 0 && String(t.id) !== activeId) {
            const b = document.createElement('span');
            b.className = 'unread-badge';
            b.textContent = t.unread;
            top.appendChild(b);
        }
        const snip = document.createElement('div');
        snip.className = 'thread-snippet';
        snip.textContent = t.last_body;
        const time = document.createElement('div');
        time.className = 'thread-time';
        time.textContent = t.last_at;
        a.append(top, snip, time);
        return a;
    }

    async function refresh() {
        try {
            const res = await fetch(list.dataset.pollUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.threads || !data.threads.length) return;
            list.replaceChildren(...data.threads.map(item));
        } catch (e) { /* coba lagi pada siklus berikutnya */ }
    }

    setInterval(refresh, 5000);
})();
</script>
@endpush
