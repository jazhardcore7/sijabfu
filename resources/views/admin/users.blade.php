@extends('layouts.app')

@section('title', 'Daftar User')
@section('breadcrumb', 'Daftar User')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="user-cog" /> Daftar User</h1>
    <p class="page-header-desc">Cari user, filter role, ubah role, serta aktifkan atau nonaktifkan akun</p>
</div>

<div class="stack">
@if(session('success'))
<x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
<x-alert type="error" data-section="user-error">{{ session('error') }}</x-alert>
@endif
@if($errors->any())
<x-alert type="error">{{ implode(' ', $errors->all()) }}</x-alert>
@endif

<div class="card card--flush">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="users" /> User</h2>
        <span class="group-count" data-total="{{ $users->total() }}">{{ $users->total() }} user</span>
    </div>

    <form action="{{ route('admin.users') }}" method="GET" class="filter-bar" role="search">
        <div class="search-input-wrapper">
            <x-icon name="search" class="search-icon" />
            <label for="user-q" class="sr-only">Cari nama, NIP, atau email</label>
            <input type="search" id="user-q" name="q" class="search-input" placeholder="Cari nama, NIP, atau email..." value="{{ request('q') }}">
        </div>
        <label for="user-role" class="sr-only">Role</label>
        <select id="user-role" name="role" class="filter-select">
            <option value="">-- Semua Role --</option>
            @foreach($roles as $key => $label)
            <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn--primary">Terapkan</button>
        @if(request('q') || request('role'))
        <a href="{{ route('admin.users') }}" class="btn btn--secondary">Reset</a>
        @endif
    </form>

    @if($users->isEmpty())
    <x-empty-state icon="users" title="Tidak ada user yang sesuai filter." />
    @else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama &amp; NIP</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Ubah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr data-user="{{ $u->id }}">
                    <td>
                        <div class="cell-strong">{{ $u->name }}</div>
                        <div class="cell-sub">NIP {{ $u->nip ?? '-' }}</div>
                    </td>
                    <td class="cell-sub">{{ $u->email }}</td>
                    <td class="nowrap">{{ $roles[$u->role] ?? $u->role }}</td>
                    <td><span class="status-pill {{ $u->is_active ? 'aktif' : 'nonaktif' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td>
                        <form action="{{ route('admin.users.update', ['user' => $u->id] + request()->only('q', 'role', 'page')) }}" method="POST" class="user-form" data-form="ubah-user">
                            @csrf
                            <label class="sr-only" for="role-{{ $u->id }}">Role {{ $u->name }}</label>
                            <select id="role-{{ $u->id }}" name="role" class="filter-select">
                                @foreach($roles as $key => $label)
                                <option value="{{ $key }}" {{ $u->role === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <label class="sr-only" for="aktif-{{ $u->id }}">Status {{ $u->name }}</label>
                            <select id="aktif-{{ $u->id }}" name="is_active" class="filter-select">
                                <option value="1" {{ $u->is_active ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ $u->is_active ? '' : 'selected' }}>Nonaktif</option>
                            </select>
                            <button type="submit" class="btn btn--primary btn--sm"><x-icon name="save" /> Simpan</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($users->hasPages())
    <div class="card-footer pagination-wrap">{{ $users->links() }}</div>
    @endif
</div>
</div>
@endsection
