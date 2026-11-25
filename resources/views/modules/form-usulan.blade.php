@extends('layouts.app')

@section('title', 'Pengajuan Usulan')
@section('breadcrumb', 'Pengajuan Usulan')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="file-plus" /> Pengajuan Usulan</h1>
    <p class="page-header-desc">Entri data, upload berkas persyaratan, lalu kirim usulan jabatan fungsional Anda</p>
</div>

@include('modules.partials.pengajuan-form', [
    'identity' => auth()->user(),
    'identitySubtitle' => 'Identitas diambil dari akun Anda; lengkapi data usulan',
    'sinkronAction' => route('pengajuan.sinkron'),
    'storeAction' => route('pengajuan.store'),
    'kirimUlangAction' => route('pengajuan.kirim-ulang'),
    'pelacakanUrl' => route('pelacakan-status'),
    'readonlyNotice' => $usulan && ! $usulan->isSelfSubmitted() ? 'Usulan ini diajukan oleh Admin Satuan Kerja Provinsi. Anda dapat memantau progresnya di menu Pelacakan Status.' : null,
])
@endsection
