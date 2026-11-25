{{--
    Badge status usulan: warna tetap per status, selalu dengan teks label (bukan warna saja).
    Pemakaian: <x-status-badge :status="$usulan->status_key" />
--}}
@props(['status' => null, 'label' => null])
@php
    $key = $status ?: 'draf';
    $text = $label ?? (\App\Models\FormUsulan::STATUS_LABELS[$status] ?? 'Draf');
@endphp
<span {{ $attributes->merge(['class' => 'status-badge']) }} data-status="{{ $key }}">{{ $text }}</span>
