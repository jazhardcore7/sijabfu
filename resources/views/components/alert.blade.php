{{--
    Pesan sukses / galat / peringatan / info.
    Pemakaian: <x-alert type="success">Tersimpan.</x-alert>
--}}
@props(['type' => 'info', 'title' => null])
@php
    $icons = ['success' => 'check-circle', 'error' => 'alert-circle', 'warning' => 'alert-triangle', 'info' => 'info'];
    $role = in_array($type, ['error', 'warning'], true) ? 'alert' : 'status';
@endphp
<div {{ $attributes->merge(['class' => 'alert alert--' . $type]) }} role="{{ $role }}">
    <x-icon :name="$icons[$type] ?? 'info'" />
    <div>
        @if($title)<div class="alert-title">{{ $title }}</div>@endif
        {{ $slot }}
    </div>
</div>
