@extends('layouts.app')

@section('title', 'Barang')
@section('page-title', 'Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Daftar Barang</h5>
    @if(auth()->user()->isGudang())
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
        <i class="bi bi-plus-lg me-1"></i> Tambah Barang
    </button>
    @endif
</div>

{{-- Filter & Search --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('barang.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari nama / kode barang..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="kategori_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status_stok" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="normal"    {{ request('status_stok') == 'normal'    ? 'selected' : '' }}>Normal</option>
                    <option value="menipis"   {{ request('status_stok') == 'menipis'   ? 'selected' : '' }}>Menipis</option>
                    <option value="habis"     {{ request('status_stok') == 'habis'     ? 'selected' : '' }}>Habis</option>
                    <option value="overstock" {{ request('status_stok') == 'overstock' ? 'selected' : '' }}>Overstock</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangs as $barang)
                    <tr>
                        <td>{{ $barangs->firstItem() + $loop->index }}</td>
                        <td><code>{{ $barang->kode_barang }}</code></td>
                        <td>{{ $barang->nama_barang }}</td>
                        <td>{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                        <td>{{ $barang->satuan }}</td>
                        <td class="text-end">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $barang->stok_saat_ini }}</td>
                        <td class="text-center">
                            @php $status = $barang->status_stok; @endphp
                            <span class="badge
                                @if($status == 'normal')    bg-success
                                @elseif($status == 'menipis')  bg-warning text-dark
                                @elseif($status == 'habis')    bg-danger
                                @elseif($status == 'overstock') bg-info text-dark
                                @endif">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('barang.show', $barang->id) }}"
                               class="btn btn-outline-info btn-sm" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->isGudang())
                            <a href="{{ route('barang.edit', $barang->id) }}"
                               class="btn btn-outline-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('barang.destroy', $barang->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus barang ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Tidak ada data barang.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($barangs->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $barangs->firstItem() }}–{{ $barangs->lastItem() }}
            dari {{ $barangs->total() }} barang
        </small>
        {{ $barangs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Modal Tambah Barang (khusus Bagian Gudang) --}}
@if(auth()->user()->isGudang())
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-labelledby="modalTambahBarangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('barang.store') }}" method="POST" id="formTambahBarang"
                onsubmit="if (document.getElementById('kodeBarangModal').value.indexOf('???') >= 0) generateKodeModal();">
                @csrf
                <input type="hidden" name="tambah_cepat" value="1">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTambahBarangLabel">
                        <i class="bi bi-plus-circle me-1 text-primary"></i> Tambah Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    @if($errors->any() && old('tambah_cepat'))
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="kode_barang" id="kodeBarangModal"
                                    class="form-control @error('kode_barang') is-invalid @enderror"
                                    value="{{ old('kode_barang') }}" readonly required>
                                <button type="button" class="btn btn-outline-primary" onclick="generateKodeModal()">
                                    <i class="bi bi-arrow-repeat"></i> Generate
                                </button>
                                @error('kode_barang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <small class="text-muted">Otomatis berupa singkatan nama barang</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" id="namaBarangModal"
                                class="form-control @error('nama_barang') is-invalid @enderror"
                                value="{{ old('nama_barang') }}" required
                                oninput="previewKodeModal()">
                            @error('nama_barang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori_id" id="kategoriIdModal"
                                class="form-select @error('kategori_id') is-invalid @enderror" required
                                onchange="previewKodeModal()">
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
                            <small class="text-muted">Supplier yang dihubungi saat stok barang ini habis</small>
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
                            <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <a href="{{ route('barang.create') }}" class="btn btn-link btn-sm text-muted text-decoration-none me-auto">
                        Buka form lengkap <i class="bi bi-arrow-right"></i>
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanBarang">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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

function previewKodeModal() {
    var nama = document.getElementById('namaBarangModal').value;
    if (nama.length >= 2) {
        document.getElementById('kodeBarangModal').value = getNamaAbbr(nama) + '-???';
    }
}

function generateKodeModal() {
    var nama = document.getElementById('namaBarangModal').value;

    if (!nama || nama.length < 2) {
        alert('Masukkan nama barang minimal 2 karakter!');
        return;
    }

    var prefix = getNamaAbbr(nama) + '-';
    var nomor = String(Date.now()).slice(-3);
    document.getElementById('kodeBarangModal').value = prefix + nomor;
}

document.addEventListener('DOMContentLoaded', function () {
    // Buka modal otomatis saat validasi gagal (form tambah cepat)
    @if(old('tambah_cepat'))
        var modal = document.getElementById('modalTambahBarang');
        if (modal) {
            new bootstrap.Modal(modal).show();
        }
    @endif

    // Loading state pada tombol simpan
    var form = document.getElementById('formTambahBarang');
    var btn = document.getElementById('btnSimpanBarang');
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        });
    }
});
</script>
@endpush
@endsection
