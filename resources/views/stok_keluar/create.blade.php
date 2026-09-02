@extends('layouts.app')

@section('title', 'Catat Stok Keluar')
@section('page-title', 'Catat Stok Keluar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Form Catat Stok Keluar</h5>
    <a href="{{ route('stok-keluar.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<form action="{{ route('stok-keluar.store') }}" method="POST" id="formStokKeluar">
@csrf

<div class="row g-3">

    {{-- Info Transaksi --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Informasi Transaksi</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. Transaksi</label>
                        <input type="text" class="form-control bg-light"
                            value="{{ $noTransaksi }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Keluar <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_keluar"
                            class="form-control @error('tanggal_keluar') is-invalid @enderror"
                            value="{{ old('tanggal_keluar', date('Y-m-d')) }}" required>
                        @error('tanggal_keluar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Penerima / Pelanggan</label>
                        <input type="text" name="nama_pelanggan" class="form-control"
                            value="{{ old('nama_pelanggan') }}"
                            placeholder="Nama penerima (opsional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jenis Keluar</label>
                        <select name="jenis_keluar" class="form-select">
                            <option value="penjualan"   {{ old('jenis_keluar') == 'penjualan'   ? 'selected' : '' }}>Penjualan</option>
                            <option value="pemakaian"   {{ old('jenis_keluar') == 'pemakaian'   ? 'selected' : '' }}>Pemakaian Internal</option>
                            <option value="retur"       {{ old('jenis_keluar') == 'retur'       ? 'selected' : '' }}>Retur ke Supplier</option>
                            <option value="rusak"       {{ old('jenis_keluar') == 'rusak'       ? 'selected' : '' }}>Barang Rusak/Expired</option>
                            <option value="lainnya"     {{ old('jenis_keluar') == 'lainnya'     ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"
                            placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Ringkasan</div>
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Item</span>
                        <span class="fw-bold" id="totalItem">0 item</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Qty</span>
                        <span class="fw-bold" id="totalQty">0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Nilai</span>
                        <span class="fw-bold text-danger" id="totalNilai">Rp 0</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger mt-3 w-100">
                    <i class="bi bi-save me-1"></i> Simpan Stok Keluar
                </button>
            </div>
        </div>
    </div>

    {{-- Detail Barang --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Detail Barang</span>
                <button type="button" class="btn btn-success btn-sm" id="btnTambahBaris">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="tabelDetail">
                        <thead class="table-light">
                            <tr>
                                <th width="35%">Barang</th>
                                <th width="15%">Stok Tersedia</th>
                                <th width="15%">Jumlah</th>
                                <th width="20%">Harga Jual</th>
                                <th width="15%">Subtotal</th>
                                <th class="text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="bodyDetail">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @error('details')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
const barangs = JSON.parse('{!! addslashes(json_encode($barangs)) !!}');
let index = 0;

function formatRupiah(angka) {
    return 'Rp ' + parseInt(angka || 0).toLocaleString('id-ID');
}

function hitungSummary() {
    const rows = document.querySelectorAll('#bodyDetail tr');
    let totalItem  = rows.length;
    let totalQty   = 0;
    let totalNilai = 0;

    rows.forEach(function(row) {
        const qty   = parseInt(row.querySelector('.input-jumlah') ? row.querySelector('.input-jumlah').value : 0);
        const harga = parseFloat(row.querySelector('.input-harga') ? row.querySelector('.input-harga').value : 0);
        totalQty   += qty;
        totalNilai += qty * harga;

        const subtotalEl = row.querySelector('.subtotal');
        if (subtotalEl) subtotalEl.textContent = formatRupiah(qty * harga);
    });

    document.getElementById('totalItem').textContent  = totalItem + ' item';
    document.getElementById('totalQty').textContent   = totalQty;
    document.getElementById('totalNilai').textContent = formatRupiah(totalNilai);
}

function buatOptionBarang() {
    let options = '<option value="">-- Pilih Barang --</option>';
    barangs.forEach(function(b) {
        options += '<option value="' + b.id + '"'
                 + ' data-harga="' + b.harga_jual + '"'
                 + ' data-stok="' + b.stok_saat_ini + '">'
                 + b.nama_barang + ' (' + b.kode_barang + ') - Stok: ' + b.stok_saat_ini
                 + '</option>';
    });
    return options;
}

function tambahBaris() {
    const tbody = document.getElementById('bodyDetail');

    const tr = document.createElement('tr');
    tr.innerHTML =
        '<td>' +
            '<select name="details[' + index + '][barang_id]" class="form-select form-select-sm select-barang" required>' +
                buatOptionBarang() +
            '</select>' +
        '</td>' +
        '<td class="stok-tersedia text-muted">-</td>' +
        '<td>' +
            '<input type="number" name="details[' + index + '][jumlah]" ' +
                'class="form-control form-control-sm input-jumlah" value="1" min="1" required>' +
        '</td>' +
        '<td>' +
            '<input type="number" name="details[' + index + '][harga_jual]" ' +
                'class="form-control form-control-sm input-harga" value="0" min="0" required>' +
        '</td>' +
        '<td class="subtotal text-muted">Rp 0</td>' +
        '<td class="text-center">' +
            '<button type="button" class="btn btn-outline-danger btn-sm btn-hapus">' +
                '<i class="bi bi-trash"></i>' +
            '</button>' +
        '</td>';

    tr.querySelector('.select-barang').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const harga = selected.getAttribute('data-harga') || 0;
        const stok  = selected.getAttribute('data-stok') || 0;
        tr.querySelector('.input-harga').value = harga;
        tr.querySelector('.stok-tersedia').textContent = stok;
        tr.querySelector('.input-jumlah').max = stok;
        hitungSummary();
    });

    tr.querySelector('.input-jumlah').addEventListener('input', function() {
        const max = parseInt(this.max || 0);
        if (max > 0 && parseInt(this.value) > max) {
            this.value = max;
            alert('Jumlah melebihi stok tersedia!');
        }
        hitungSummary();
    });

    tr.querySelector('.input-harga').addEventListener('input', hitungSummary);

    tr.querySelector('.btn-hapus').addEventListener('click', function() {
        tr.remove();
        hitungSummary();
    });

    tbody.appendChild(tr);
    index++;
    hitungSummary();
}

document.getElementById('btnTambahBaris').addEventListener('click', tambahBaris);

document.getElementById('formStokKeluar').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#bodyDetail tr');
    if (rows.length === 0) {
        e.preventDefault();
        alert('Minimal tambahkan 1 barang!');
    }
});

tambahBaris();
</script>
@endpush