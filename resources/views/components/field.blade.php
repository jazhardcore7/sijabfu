{{--
    Bidang formulir: label, kontrol (slot), petunjuk, dan pesan galat di dekat bidang.
    Pemakaian:
    <x-field name="judul" label="Judul / Tentang" required hint="...">
        <input id="judul" name="judul" aria-describedby="judul-hint judul-error" ...>
    </x-field>
--}}
@props(['name', 'label', 'for' => null, 'required' => false, 'hint' => null])
@php
    $id = $for ?? $name;
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp
<div {{ $attributes->merge(['class' => 'field' . ($errors->has($errorKey) ? ' has-error' : '')]) }}>
    <label class="field-label" for="{{ $id }}">
        {{ $label }}@if($required)<span class="field-required" aria-hidden="true">*</span>@endif
    </label>
    {{ $slot }}
    @if($hint)
    <div class="field-hint" id="{{ $id }}-hint">{{ $hint }}</div>
    @endif
    @error($errorKey)
    <div class="field-error" id="{{ $id }}-error">{{ $message }}</div>
    @enderror
</div>
