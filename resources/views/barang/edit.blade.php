@extends('layouts.app')

@section('title', 'Edit Barang')
@section('page-title', 'Edit Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Edit Barang</h5>
    <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('barang.update', $barang->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode Barang</label>
                    <div class="input-group">
                        <input type="text" name="kode_barang" id="kodeBarang"
                            class="form-control @error('kode_barang') is-invalid @enderror"
                            value="{{ old('kode_barang', $barang->kode_barang) }}" required>
                        <button type="button" class="btn btn-outline-primary" onclick="generateKode()">
                            <i class="bi bi-arrow-repeat"></i> Generate
                        </button>
                        @error('kode_barang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <small class="text-muted">Singkatan nama barang + nomor, contoh: <code>SP50-001</code>.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Barang</label>
                    <input type="text" name="nama_barang" id="namaBarang"
                        class="form-control @error('nama_barang') is-invalid @enderror"
                        value="{{ old('nama_barang', $barang->nama_barang) }}" required>
                    @error('nama_barang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ old('kategori_id', $barang->kategori_id) == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Satuan</label>
                    <select name="satuan" class="form-select @error('satuan') is-invalid @enderror" required>
                        <option value="">-- Pilih Satuan --</option>
                        @php $satuan = old('satuan', $barang->satuan); @endphp
                        <option value="pcs" {{ $satuan == 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="sak" {{ $satuan == 'sak' ? 'selected' : '' }}>Sak</option>
                        <option value="kg" {{ $satuan == 'kg' ? 'selected' : '' }}>Kg</option>
                        <option value="m" {{ $satuan == 'm' ? 'selected' : '' }}>Meter</option>
                        <option value="m2" {{ $satuan == 'm2' ? 'selected' : '' }}>M&sup2;</option>
                        <option value="m3" {{ $satuan == 'm3' ? 'selected' : '' }}>M&sup3;</option>
                        <option value="liter" {{ $satuan == 'liter' ? 'selected' : '' }}>Liter</option>
                        <option value="dus" {{ $satuan == 'dus' ? 'selected' : '' }}>Dus</option>
                        <option value="kardus" {{ $satuan == 'kardus' ? 'selected' : '' }}>Kardus</option>
                        <option value="botol" {{ $satuan == 'botol' ? 'selected' : '' }}>Botol</option>
                        <option value="kaleng" {{ $satuan == 'kaleng' ? 'selected' : '' }}>Kaleng</option>
                        <option value="lembar" {{ $satuan == 'lembar' ? 'selected' : '' }}>Lembar</option>
                        <option value="ubin" {{ $satuan == 'ubin' ? 'selected' : '' }}>Ubin</option>
                        <option value="buah" {{ $satuan == 'buah' ? 'selected' : '' }}>Buah</option>
                        <option value="roll" {{ $satuan == 'roll' ? 'selected' : '' }}>Roll</option>
                        <option value="others" {{ $satuan == 'others' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga Beli</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="harga_beli" class="form-control @error('harga_beli') is-invalid @enderror"
                            value="{{ old('harga_beli', $barang->harga_beli) }}" min="0" required>
                        @error('harga_beli')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga Jual</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="harga_jual" class="form-control @error('harga_jual') is-invalid @enderror"
                            value="{{ old('harga_jual', $barang->harga_jual) }}" min="0" required>
                        @error('harga_jual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok Saat Ini</label>
                    <input type="number" name="stok_saat_ini" class="form-control @error('stok_saat_ini') is-invalid @enderror"
                        value="{{ old('stok_saat_ini', $barang->stok_saat_ini) }}" min="0" required>
                    @error('stok_saat_ini')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok Minimum</label>
                    <input type="number" name="stok_minimum" class="form-control @error('stok_minimum') is-invalid @enderror"
                        value="{{ old('stok_minimum', $barang->stok_minimum) }}" min="0" required>
                    @error('stok_minimum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok Maksimum</label>
                    <input type="number" name="stok_maksimum" class="form-control @error('stok_maksimum') is-invalid @enderror"
                        value="{{ old('stok_maksimum', $barang->stok_maksimum) }}" min="0">
                    @error('stok_maksimum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Supplier Preferensi (untuk restok)</label>
                    <select name="preferred_supplier_id" class="form-select @error('preferred_supplier_id') is-invalid @enderror">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('preferred_supplier_id', $barang->preferred_supplier_id) == $sup->id ? 'selected' : '' }}>
                                {{ $sup->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Supplier yang akan dihubungi saat stok barang ini habis</small>
                    @error('preferred_supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lokasi Rak</label>
                    <input type="text" name="lokasi_rak" class="form-control @error('lokasi_rak') is-invalid @enderror"
                        value="{{ old('lokasi_rak', $barang->lokasi_rak) }}" placeholder="Misal: A-01, B-02">
                    @error('lokasi_rak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"
                        placeholder="Deskripsi barang (opsional)">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
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

function generateKode() {
    var nama = document.getElementById('namaBarang').value;
    if (!nama || nama.length < 2) {
        alert('Masukkan nama barang minimal 2 karakter!');
        return;
    }
    var prefix = getNamaAbbr(nama) + '-';
    var nomor = String(Date.now()).slice(-3);
    document.getElementById('kodeBarang').value = prefix + nomor;
}
</script>
@endpush
@endsection
