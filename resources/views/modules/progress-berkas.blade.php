@extends('layouts.app')

@section('title', 'Pelacakan Status')
@section('breadcrumb', 'Pelacakan Status')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="activity" /> Pelacakan Status</h1>
    <p class="page-header-desc">Pantau status usulan, unduh surat usulan, dan lihat sertifikat UKOM Anda</p>
</div>

@if(session('success'))
<x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if($usulan)

@include('modules.partials.pelacakan-detail')

@else

<div class="card">
    @if($hasDraft)
    <x-empty-state icon="file-text" title="Usulan Belum Dikirim">
        Anda memiliki draf usulan yang belum dikirim. Lengkapi berkas persyaratan lalu klik "Kirim Usulan".
        <x-slot:action><a href="{{ route('pengajuan') }}" class="btn btn--primary"><x-icon name="file-plus" /> Pengajuan Usulan</a></x-slot:action>
    </x-empty-state>
    @else
    <x-empty-state icon="inbox" title="Belum Ada Usulan">
        @can('create', \App\Models\FormUsulan::class)
        Anda belum mengajukan usulan jabatan fungsional. Silakan lakukan Pengajuan Usulan terlebih dahulu.
        <x-slot:action><a href="{{ route('pengajuan') }}" class="btn btn--primary"><x-icon name="file-plus" /> Pengajuan Usulan</a></x-slot:action>
        @else
        Belum ada usulan yang diajukan Admin Satuan Kerja untuk Anda.
        @endcan
    </x-empty-state>
    @endif
</div>

@endif
@endsection
