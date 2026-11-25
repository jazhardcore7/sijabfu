{{-- Filter satuan kerja dan provinsi pegawai untuk daftar staf (FR-SATKER-18). Parameter: $prefix (id unik). --}}
<label for="{{ $prefix }}-satker" class="sr-only">Satuan kerja</label>
<select id="{{ $prefix }}-satker" name="satker" class="filter-select" data-filter="satker">
    <option value="">-- Semua Satker --</option>
    @foreach(\App\Models\User::SATKER_LABELS as $key => $label)
    <option value="{{ $key }}" {{ request('satker') === $key ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
</select>
<label for="{{ $prefix }}-provinsi" class="sr-only">Provinsi</label>
<select id="{{ $prefix }}-provinsi" name="provinsi" class="filter-select" data-filter="provinsi">
    <option value="">-- Semua Provinsi --</option>
    @foreach(config('sijabfu.provinsi') as $kode => $nama)
    <option value="{{ $kode }}" {{ (string) request('provinsi') === (string) $kode ? 'selected' : '' }}>{{ $kode }} {{ $nama }}</option>
    @endforeach
</select>
