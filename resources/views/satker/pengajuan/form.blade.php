@extends('layouts.app')

@section('title', 'Pengajuan Usulan')
@section('breadcrumb', 'Pengajuan Usulan')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="file-plus" /> Pengajuan Usulan: {{ $pegawai->name }}</h1>
    <p class="page-header-desc">
        Diajukan oleh Admin Satuan Kerja Provinsi untuk {{ $pegawai->name }} (NIP {{ $pegawai->nip }}).
        <a href="{{ route('satker.pengajuan.index') }}">Kembali ke daftar usulan</a>
    </p>
</div>

@include('modules.partials.pengajuan-form', [
    'identity' => $pegawai,
    'identitySubtitle' => 'Identitas diambil dari data pegawai yang diusulkan; lengkapi data usulan',
    'sinkronAction' => route('satker.pengajuan.sinkron', ['pegawai' => $pegawai->id] + ($usulan ? ['usulan' => $usulan->id] : [])),
    'storeAction' => $usulan ? route('satker.pengajuan.update', $usulan) : route('satker.pengajuan.store'),
    'hiddenFields' => $usulan ? [] : ['pegawai_id' => $pegawai->id],
    'kirimUlangAction' => $usulan ? route('satker.pengajuan.kirim-ulang', $usulan) : '#',
    'pelacakanUrl' => route('satker.pengajuan.index'),
])
@endsection
