@extends('layouts.app')

@section('title', 'Form Retur ke Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Form Retur ke Supplier</h5>
    <a href="{{ route('retur.index', ['tab' => 'supplier']) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

{{-- Notifikasi gagal: data belum lengkap --}}
@if($errors->any())
<div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-2" role="alert">
    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
    <div>
        <strong>Lengkapi data retur terlebih dahulu:</strong>
        <ul class="mb-0 mt-1 ps-3 small">
            @foreach($errors->all() as $pesan)
            <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-4">
                <small class="text-muted d-block">No. Nota Stok Masuk</small>
                <strong><code>{{ $stokMasuk->no_transaksi }}</code></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Tanggal</small>
                <strong>{{ \Carbon\Carbon::parse($stokMasuk->tanggal_masuk)->format('d/m/Y') }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Supplier</small>
                <strong>{{ $stokMasuk->supplier->nama_supplier ?? '-' }}</strong>
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Nota Supplier</small>
                <strong>{{ $stokMasuk->no_nota_supplier ?? '-' }}</strong>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('retur.supplier-proses', $stokMasuk->id) }}" method="POST" id="formReturSupplier">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
            <span>Pilih Barang yang akan diretur ke supplier</span>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="terapkanSemuaAlasan()">
                <i class="bi bi-arrow-repeat me-1"></i> Terapkan alasan ke semua
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Barang</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Jumlah Diterima</th>
                            <th class="text-end">Harga Beli</th>
                            <th class="text-center">Sudah Diretur</th>
                            <th class="text-center">Jumlah Retur</th>
                            <th style="min-width:190px;">Alasan Retur <span class="text-danger">*</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stokMasuk->details as $index => $detail)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $detail->barang->nama_barang }}
                                <br><small class="text-muted">{{ $detail->barang->kode_barang }}</small>
                            </td>
                            <td class="text-center">{{ $detail->barang->satuan }}</td>
                            <td class="text-center">{{ $detail->jumlah }} {{ $detail->barang->satuan }}</td>
                            <td class="text-end">Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($detail->jumlah_diretur > 0)
                                <span class="badge bg-warning-subtle text-warning-emphasis">{{ $detail->jumlah_diretur }}</span>
                                <small class="text-muted d-block">Sisa: {{ $detail->sisaRetur }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center" style="width:110px;">
                                <input type="hidden" name="items[{{ $index }}][detail_id]" value="{{ $detail->id }}">
                                @if($detail->sisaRetur > 0)
                                <input type="number" name="items[{{ $index }}][jumlah_kembali]"
                                    id="jumlahKembali{{ $index }}"
                                    class="form-control form-control-sm text-center"
                                    min="0" max="{{ $detail->sisaRetur }}" value="0"
                                    oninput="hitungTotalRetur()" onchange="hitungTotalRetur()">
                                @else
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Diretur Semua</span>
                                @endif
                            </td>
                            <td>
                                <select name="items[{{ $index }}][alasan]" id="alasanSel{{ $index }}"
                                    class="form-select form-select-sm" onchange="toggleAlasanLainnya({{ $index }})">
                                    <option value="">-- Pilih Alasan --</option>
                                    <option value="Barang Rusak">Barang Rusak</option>
                                    <option value="Salah Kirim / Tidak Sesuai">Salah Kirim / Tidak Sesuai</option>
                                    <option value="Kadaluarsa / Expired">Kadaluarsa / Expired</option>
                                    <option value="Barang Tidak Laku">Barang Tidak Laku</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <div id="divLainnya{{ $index }}" class="mt-1 d-none">
                                    <input type="text" name="items[{{ $index }}][alasan_lainnya]"
                                        id="alasanLainnya{{ $index }}" class="form-control form-control-sm"
                                        placeholder="Tuliskan alasan lainnya...">
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('items.*.alasan')
            <div class="text-danger small px-3 pb-2">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
            </div>
            @enderror
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <span class="text-muted small">Barang diretur:</span>
                    <span id="totalReturBarang" class="fw-bold ms-2">0 item</span>
                </div>
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>Stok barang akan dikurangi sesuai jumlah retur.
                </div>
                <button type="submit" class="btn btn-warning" onclick="return confirm('Yakin ingin menyimpan retur? Stok akan dikurangi.')">
                    <i class="bi bi-arrow-return-left me-1"></i> Simpan Retur
                </button>
            </div>
            @error('items')
            <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>
</form>

@push('scripts')
<script>
function hitungTotalRetur() {
    var inputs = document.querySelectorAll('[name*="jumlah_kembali"]');
    var total = 0;
    for (var i = 0; i < inputs.length; i++) {
        total += parseInt(inputs[i].value) || 0;
    }
    document.getElementById('totalReturBarang').textContent = total + ' item';
}

function toggleAlasanLainnya(idx) {
    var sel = document.getElementById('alasanSel' + idx);
    var div = document.getElementById('divLainnya' + idx);
    var isLainnya = sel.value === 'Lainnya';
    div.classList.toggle('d-none', !isLainnya);
    if (isLainnya) {
        document.getElementById('alasanLainnya' + idx).focus();
    }
}

// Salin alasan yang sedang dipilih di baris pertama ke semua baris lainnya
function terapkanSemuaAlasan() {
    var firstSel = document.getElementById('alasanSel0');
    if (!firstSel || !firstSel.value) {
        alert('Pilih dulu alasan pada baris pertama, lalu klik tombol ini untuk menyalin ke semua barang.');
        return;
    }
    var i = 0;
    while (document.getElementById('alasanSel' + i)) {
        var sel = document.getElementById('alasanSel' + i);
        sel.value = firstSel.value;
        toggleAlasanLainnya(i);
        i++;
    }
}

// Inisialisasi
hitungTotalRetur();
var init = 0;
while (document.getElementById('alasanSel' + init)) {
    if (document.getElementById('alasanSel' + init).value) toggleAlasanLainnya(init);
    init++;
}

// Validasi sebelum submit:
// - minimal 1 barang dengan jumlah retur > 0
// - setiap barang yang diretur wajib punya alasan
// - alasan "Lainnya" wajib diisi keterangannya
document.getElementById('formReturSupplier').addEventListener('submit', function(e) {
    var adaItem = false;
    var i = 0;
    while (document.getElementById('alasanSel' + i)) {
        var jml = document.getElementById('jumlahKembali' + i);
        var jumlah = parseInt(jml ? jml.value : 0) || 0;
        if (jumlah <= 0) { i++; continue; }
        adaItem = true;

        var sel = document.getElementById('alasanSel' + i);
        if (!sel.value) {
            e.preventDefault();
            alert('Pilih alasan retur untuk setiap barang yang diretur.');
            return;
        }
        if (sel.value === 'Lainnya') {
            var inp = document.getElementById('alasanLainnya' + i);
            if (!inp.value.trim()) {
                e.preventDefault();
                alert('Tuliskan alasan retur pada kolom "Alasan Lainnya" untuk barang yang diretur.');
                return;
            }
        }
        i++;
    }

    if (!adaItem) {
        e.preventDefault();
        alert('Pilih minimal 1 barang dengan jumlah retur lebih dari 0.');
    }
});
</script>
@endpush
@endsection
