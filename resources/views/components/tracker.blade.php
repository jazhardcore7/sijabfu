{{--
    Tracker tahapan usulan. Setiap tahap punya teks label dan teks keadaan (bukan warna saja).
    Pemakaian: <x-tracker :steps="[['key' => 'verifikasi', 'label' => 'Verifikasi (Perbaikan)', 'state' => 'active'], ...]" />
    state: done | active | perbaikan | pending
--}}
@props(['steps' => []])
@php
    $stateText = ['done' => 'Selesai', 'active' => 'Sedang Diproses', 'perbaikan' => 'Perbaikan', 'pending' => 'Belum Mulai'];
@endphp
<ol {{ $attributes->merge(['class' => 'tracker']) }} style="--tracker-steps: {{ max(count($steps), 1) }};">
    @foreach($steps as $i => $step)
    <li class="tracker-step" data-stage="{{ $step['key'] }}" data-state="{{ $step['state'] }}">
        <span class="tracker-dot">
            @if($step['state'] === 'done')<x-icon name="check" />@else{{ $i + 1 }}@endif
        </span>
        <span>
            {{ $step['label'] }}
            <span class="tracker-state">· {{ $stateText[$step['state']] ?? '' }}</span>
        </span>
    </li>
    @endforeach
</ol>
