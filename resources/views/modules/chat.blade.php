@extends('layouts.app')

@section('title', 'Chat Box')
@section('breadcrumb', 'Chat Box')

@section('content')
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">💬 Chat Box</h1>
    <p class="page-header-desc">Komunikasi 2 arah dengan Tim Pembinaan JF. Pesan baru dimuat otomatis setiap 5 detik.</p>
    <div class="page-header-divider"></div>
</div>

<div style="max-width: 860px;">
    @include('chat._conversation', [
        'messages' => $messages,
        'viewer' => $viewer,
        'pollUrl' => route('chat.index'),
        'postUrl' => route('chat.store'),
        'emptyText' => 'Belum ada pesan. Tulis pertanyaan Anda untuk Tim Pembinaan JF.',
    ])
</div>
@endsection
