@extends('layouts.app')

@section('title', 'Pelacakan Status')
@section('breadcrumb', 'Pelacakan Status')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="activity" /> Pelacakan Status: {{ $usulan->nama_lengkap }}</h1>
    <p class="page-header-desc"><a href="{{ route('satker.pelacakan.index') }}">Kembali ke daftar usulan</a></p>
</div>

@include('modules.partials.pelacakan-detail')
@endsection
