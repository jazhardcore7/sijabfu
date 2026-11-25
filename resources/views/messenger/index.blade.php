@extends('layouts.app')

@section('title', 'Messenger')
@section('breadcrumb', 'Chat Box (Messenger)')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="message" /> Messenger</h1>
    <p class="page-header-desc">Daftar pesan dari pegawai untuk berkomunikasi. Inbox dipakai bersama seluruh Tim Pembinaan JF.</p>
</div>

<div class="messenger {{ $active ? 'has-active' : '' }}">
    <nav class="thread-list" aria-labelledby="thread-list-title">
        <h2 class="thread-list-header" id="thread-list-title"><x-icon name="inbox" /> Daftar Pesan dari Pegawai</h2>
        <div id="threadList" data-poll-url="{{ route('messenger.index') }}" data-active="{{ $active?->id }}">
            @forelse($threads as $t)
            <a href="{{ route('messenger.show', $t) }}" class="thread-item {{ $active && $active->id === $t->id ? 'active' : '' }}" data-thread="{{ $t->id }}" @if($active && $active->id === $t->id) aria-current="page" @endif>
                <div class="thread-top">
                    <span class="thread-name">{{ $t->pegawai?->name }}</span>
                    @if($t->unread_count > 0)
                    <span class="unread-badge" data-unread="{{ $t->unread_count }}" aria-label="{{ $t->unread_count }} pesan belum dibaca">{{ $t->unread_count }}</span>
                    @endif
                </div>
                <div class="thread-snippet">{{ \Illuminate\Support\Str::limit((string) $t->latestMessage?->body, 80) }}</div>
                <div class="thread-time">{{ $t->last_message_at?->format('d M Y H:i') }}</div>
            </a>
            @empty
            <div class="thread-empty">Belum ada pesan dari pegawai.</div>
            @endforelse
        </div>
    </nav>

    <div class="messenger-main">
        @if($active)
        <div class="active-header" data-active-thread="{{ $active->id }}">
            <a href="{{ route('messenger.index') }}" class="btn btn--ghost btn--sm messenger-back"><x-icon name="arrow-left" /><span class="sr-only">Daftar Pesan dari Pegawai</span></a>
            <span class="tim-avatar" aria-hidden="true">{{ strtoupper(mb_substr((string) $active->pegawai?->name, 0, 2)) }}</span>
            <div class="min-w-0">
                <div class="cell-strong">{{ $active->pegawai?->name }}</div>
                <div class="cell-sub">NIP {{ $active->pegawai?->nip }}</div>
            </div>
        </div>
        @include('chat._conversation', [
            'messages' => $messages,
            'viewer' => $viewer,
            'pollUrl' => route('messenger.show', $active),
            'postUrl' => route('messenger.reply', $active),
            'emptyText' => 'Belum ada pesan.',
        ])
        @else
        <div class="card messenger-empty">
            <x-empty-state icon="message" title="Pilih pesan dari daftar untuk membuka percakapan." />
        </div>
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
