{{--
    Keadaan kosong untuk daftar.
    Pemakaian: <x-empty-state icon="inbox" title="Belum ada usulan">Teks penjelasan</x-empty-state>
--}}
@props(['icon' => 'inbox', 'title'])
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-state-icon"><x-icon :name="$icon" /></div>
    <div class="empty-state-title">{{ $title }}</div>
    @if(trim($slot) !== '')
    <div class="empty-state-text">{{ $slot }}</div>
    @endif
    {{ $action ?? '' }}
</div>
