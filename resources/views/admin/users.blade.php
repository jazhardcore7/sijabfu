@extends('layouts.app')

@section('title', 'Daftar User')
@section('breadcrumb', 'Daftar User')

@include('verifikasi._styles')

@push('styles')
<style>
    .user-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .user-form .vb-select { padding: 7px 10px; }
    .status-pill { display: inline-flex; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; }
    .status-pill.aktif { background: #D1FAE5; color: #065F46; }
    .status-pill.nonaktif { background: #FEE2E2; color: #991B1B; }
</style>
@endpush

@section('content')
<div class="page-header animate-fade-in vb-header">
    <h1 class="page-header-title">👤 Daftar User</h1>
    <p class="page-header-desc">Cari user, filter role, ubah role, serta aktifkan atau nonaktifkan akun</p>
    <div class="page-header-divider"></div>
</div>

@if(session('success'))
<div class="vb-alert success"><span>✅</span><span>{{ session('success') }}</span></div>
@endif
@if(session('error'))
<div class="vb-alert error" data-section="user-error"><span>⚠️</span><span>{{ session('error') }}</span></div>
@endif
@if($errors->any())
<div class="vb-alert error"><span>⚠️</span><span>{{ implode(' ', $errors->all()) }}</span></div>
@endif

<div class="vb-card">
    <div class="vb-card-header">
        <span>👥 User</span>
        <span class="vb-muted" data-total="{{ $users->total() }}">{{ $users->total() }} user</span>
    </div>

    <form action="{{ route('admin.users') }}" method="GET" class="vb-filter">
        <input type="text" name="q" class="vb-input" placeholder="Cari nama, NIP, atau email..." value="{{ request('q') }}">
        <select name="role" class="vb-select">
            <option value="">-- Semua Role --</option>
            @foreach($roles as $key => $label)
            <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="vb-btn">Terapkan</button>
        @if(request('q') || request('role'))
        <a href="{{ route('admin.users') }}" class="vb-btn secondary">Reset</a>
        @endif
    </form>

    <table class="vb-table">
        <thead>
            <tr>
                <th>Nama & NIP</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Ubah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr data-user="{{ $u->id }}">
                <td>
                    <div style="font-weight:700;">{{ $u->name }}</div>
                    <div class="vb-muted">NIP {{ $u->nip ?? '-' }}</div>
                </td>
                <td class="vb-muted">{{ $u->email }}</td>
                <td>{{ $roles[$u->role] ?? $u->role }}</td>
                <td><span class="status-pill {{ $u->is_active ? 'aktif' : 'nonaktif' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td>
                    <form action="{{ route('admin.users.update', ['user' => $u->id] + request()->only('q', 'role', 'page')) }}" method="POST" class="user-form" data-form="ubah-user">
                        @csrf
                        <select name="role" class="vb-select">
                            @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ $u->role === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="is_active" class="vb-select">
                            <option value="1" {{ $u->is_active ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ $u->is_active ? '' : 'selected' }}>Nonaktif</option>
                        </select>
                        <button type="submit" class="vb-btn" style="padding:7px 12px;">Simpan</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--c-text-muted);">Tidak ada user yang sesuai filter.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="vb-pagination">{{ $users->links() }}</div>
</div>
@endsection
