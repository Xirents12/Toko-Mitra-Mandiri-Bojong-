@extends('layouts.app')

@section('title', 'Tambah Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Tambah Barang</h5>
    <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('barang.store') }}" method="POST"
            onsubmit="if (document.getElementById('kodeBarang').value.indexOf('???') >= 0) generateKode();">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" name="kode_barang" id="kodeBarang"
                            class="form-control @error('kode_barang') is-invalid @enderror"
                            value="{{ old('kode_barang', $kodeBarang) }}" readonly required>
                        <button type="button" class="btn btn-outline-primary" onclick="generateKode()">
                            <i class="bi bi-arrow-repeat"></i> Generate
                        </button>
                        @error('kode_barang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <small class="text-muted">Otomatis berupa singkatan nama barang, contoh: <code>SP50-001</code></small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" name="nama_barang" id="namaBarang"
                        class="form-control @error('nama_barang') is-invalid @enderror"
                        value="{{ old('nama_barang') }}" required
                        oninput="previewKode()">
                    @error('nama_barang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori_id" id="kategoriId"
                        class="form-select @error('kategori_id') is-invalid @enderror" required
                        onchange="previewKode()">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}"
                                data-nama="{{ $kategori->nama_kategori }}"
                                {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                    <select name="satuan" class="form-select @error('satuan') is-invalid @enderror" required>
                        <option value="">-- Pilih Satuan --</option>
                        <option value="pcs" {{ old('satuan') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="sak" {{ old('satuan') == 'sak' ? 'selected' : '' }}>Sak</option>
                        <option value="kg" {{ old('satuan') == 'kg' ? 'selected' : '' }}>Kg</option>
                        <option value="m" {{ old('satuan') == 'm' ? 'selected' : '' }}>Meter</option>
                        <option value="m2" {{ old('satuan') == 'm2' ? 'selected' : '' }}>M&sup2;</option>
                        <option value="m3" {{ old('satuan') == 'm3' ? 'selected' : '' }}>M&sup3;</option>
                        <option value="liter" {{ old('satuan') == 'liter' ? 'selected' : '' }}>Liter</option>
                        <option value="dus" {{ old('satuan') == 'dus' ? 'selected' : '' }}>Dus</option>
                        <option value="kardus" {{ old('satuan') == 'kardus' ? 'selected' : '' }}>Kardus</option>
                        <option value="botol" {{ old('satuan') == 'botol' ? 'selected' : '' }}>Botol</option>
                        <option value="kaleng" {{ old('satuan') == 'kaleng' ? 'selected' : '' }}>Kaleng</option>
                        <option value="lembar" {{ old('satuan') == 'lembar' ? 'selected' : '' }}>Lembar</option>
                        <option value="ubin" {{ old('satuan') == 'ubin' ? 'selected' : '' }}>Ubin</option>
                        <option value="buah" {{ old('satuan') == 'buah' ? 'selected' : '' }}>Buah</option>
                        <option value="roll" {{ old('satuan') == 'roll' ? 'selected' : '' }}>Roll</option>
                        <option value="others" {{ old('satuan') == 'others' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga Beli <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="harga_beli"
                            class="form-control @error('harga_beli') is-invalid @enderror"
                            value="{{ old('harga_beli') }}" min="0" required>
                        @error('harga_beli')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga Jual <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="harga_jual"
                            class="form-control @error('harga_jual') is-invalid @enderror"
                            value="{{ old('harga_jual') }}" min="0" required>
                        @error('harga_jual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok Saat Ini</label>
                    <input type="number" name="stok_saat_ini"
                        class="form-control @error('stok_saat_ini') is-invalid @enderror"
                        value="{{ old('stok_saat_ini', 0) }}" min="0" required>
                    @error('stok_saat_ini')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok Minimum</label>
                    <input type="number" name="stok_minimum"
                        class="form-control @error('stok_minimum') is-invalid @enderror"
                        value="{{ old('stok_minimum', 0) }}" min="0" required>
                    @error('stok_minimum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok Maksimum</label>
                    <input type="number" name="stok_maksimum"
                        class="form-control @error('stok_maksimum') is-invalid @enderror"
                        value="{{ old('stok_maksimum', 0) }}" min="0">
                    @error('stok_maksimum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Supplier Preferensi (untuk restok)</label>
                    <select name="preferred_supplier_id" class="form-select">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('preferred_supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Supplier yang akan dihubungi saat stok barang ini habis</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lokasi Rak</label>
                    <input type="text" name="lokasi_rak"
                        class="form-control @error('lokasi_rak') is-invalid @enderror"
                        value="{{ old('lokasi_rak') }}" placeholder="Misal: A-01, B-02">
                    @error('lokasi_rak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function getNamaAbbr(nama) {
    if (!nama) return 'XXX';
    var cleaned = nama.replace(/\(.*?\)/g, ' ');
    var words = cleaned.split(/[^a-zA-Z0-9]+/);
    var abbr = '';
    for (var i = 0; i < words.length; i++) {
        var w = words[i];
        if (!w) continue;
        if (/\d/.test(w)) {
            // Kata berisi angka: ambil angkanya + huruf pertama sisanya ("50kg" -> "50K")
            abbr += w.replace(/\D/g, '');
            var letters = w.replace(/\d/g, '');
            if (letters.length > 0) abbr += letters[0].toUpperCase();
        } else {
            abbr += w[0].toUpperCase();
        }
        if (abbr.length >= 4) break;
    }
    abbr = abbr.substring(0, 4);
    if (abbr.length < 2) {
        abbr = nama.replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
    }
    return abbr.length > 0 ? abbr : 'XXX';
}

function previewKode() {
    var nama = document.getElementById('namaBarang').value;
    if (nama.length >= 2) {
        document.getElementById('kodeBarang').value = getNamaAbbr(nama) + '-???';
    }
}

function generateKode() {
    var nama = document.getElementById('namaBarang').value;

    if (!nama || nama.length < 2) {
        alert('Masukkan nama barang minimal 2 karakter!');
        return;
    }

    // Singkatan nama + nomor unik dari timestamp
    var prefix = getNamaAbbr(nama) + '-';
    var nomor = String(Date.now()).slice(-3);
    document.getElementById('kodeBarang').value = prefix + nomor;
}
</script>
@endpush
@endsection
