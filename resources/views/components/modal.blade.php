{{--
    Dialog konfirmasi berbasis <dialog> (fokus terkunci, Esc menutup).
    Pemakaian:
    <x-modal id="hapus-1" title="Hapus peraturan?">
        Teks konfirmasi
        <x-slot:footer>
            <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
            <button type="submit" form="form-hapus-1" class="btn btn--danger">Hapus</button>
        </x-slot:footer>
    </x-modal>
    Pembuka: <button type="button" data-modal-open="hapus-1">...</button>
--}}
@props(['id', 'title'])
<dialog {{ $attributes->merge(['class' => 'modal']) }} id="{{ $id }}" aria-labelledby="{{ $id }}-title">
    <div class="modal-header" id="{{ $id }}-title">{{ $title }}</div>
    <div class="modal-body">{{ $slot }}</div>
    @isset($footer)
    <div class="modal-footer">{{ $footer }}</div>
    @endisset
</dialog>
@once
@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        const opener = e.target.closest('[data-modal-open]');
        if (opener) {
            const dlg = document.getElementById(opener.getAttribute('data-modal-open'));
            if (dlg && typeof dlg.showModal === 'function') { e.preventDefault(); dlg.showModal(); }
        }
        const closer = e.target.closest('[data-modal-close]');
        if (closer) {
            const dlg = closer.closest('dialog');
            if (dlg) { dlg.close(); }
        }
    });
</script>
@endpush
@endonce
