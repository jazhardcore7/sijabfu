{{--
    Grafik batang horizontal tanpa dependensi (SVG/CSS dirender server).
    Variabel: $chartId, $title, $data (label => angka), $color (opsional).
    Data juga ditulis sebagai JSON agar dapat diuji dan dipakai ulang.
--}}
@php
    $chartMax = max(1, ...array_values($data ?: [0]));
    $chartTotal = array_sum($data);
    $chartColor = $color ?? 'var(--bps-mid)';
@endphp
<div class="dash-chart" data-chart="{{ $chartId }}">
    <div class="dash-chart-head">
        <span class="dash-chart-title">{{ $title }}</span>
        <span class="dash-chart-total">Total: {{ $chartTotal }}</span>
    </div>
    <div class="dash-chart-body" role="img" aria-label="{{ $title }}">
        @foreach($data as $label => $value)
        <div class="dash-bar-row">
            <div class="dash-bar-label" title="{{ $label }}">{{ $label }}</div>
            <div class="dash-bar-track">
                <div class="dash-bar-fill" style="width: {{ round(($value / $chartMax) * 100, 1) }}%; background: {{ $chartColor }};"></div>
            </div>
            <div class="dash-bar-value">{{ $value }}</div>
        </div>
        @endforeach
    </div>
    <script type="application/json" data-chart-json="{{ $chartId }}">@json($data)</script>
</div>
