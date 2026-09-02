@extends('layouts.app')

@section('title', 'Pesan Barang (Permintaan)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-cart-plus me-2 text-primary"></i>Pesan Barang (Permintaan ke Admin)</h5>
    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<form action="{{ route('purchase-order.store') }}" method="POST" id="formPO">
    @csrf
    <div class="row g-3">
        {{-- ═══ ITEM BARANG ═══ --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-box-seam me-1 text-primary"></i>Item Barang</span>
                    <span class="badge bg-primary rounded-pill" id="badgeJmlItem">0 barang</span>
                </div>
                <div class="card-body">
                    @if($barangKritis->isNotEmpty())
                    <div class="mb-3 p-2 rounded-3 border border-danger-subtle bg-danger bg-opacity-10">
                        <div class="small fw-semibold text-danger mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Barang Kritis / Minimum — klik untuk menambahkan ke pesanan
                        </div>
                        <div class="d-flex flex-wrap gap-1" id="daftarBarangKritis">
                            @foreach($barangs as $i => $b)
                            @if(in_array($b->id, $kritisIds))
                            <button type="button" class="btn btn-sm btn-outline-danger badge-kritis-pilih"
                                    data-opt-idx="{{ $i + 1 }}">
                                <i class="bi bi-plus-circle me-1"></i>{{ $b->nama_barang }}
                                <small class="opacity-75">(sisa {{ $b->stok_saat_ini }} / min {{ $b->stok_minimum }})</small>
                            </button>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="mb-3">
                        <div class="input-group">
                            <div class="position-relative flex-grow-1">
                                <input type="text" id="pencarianBarang" class="form-control" placeholder="Ketik nama / kode barang, lalu klik hasilnya untuk langsung memesan..." autocomplete="off">
                                <div id="hasilPencarian" class="list-group position-absolute w-100 d-none shadow-sm"
                                    style="z-index:1050; max-height:280px; overflow-y:auto; border-radius:.5rem;"></div>
                            </div>
                        </div>
                        {{-- Select tersembunyi: sumber data untuk pencarian & daftar pesan otomatis --}}
                        <select id="pilihBarang" class="d-none" aria-hidden="true" tabindex="-1">
                            <option value="">-- Pilih Barang --</option>
                        @foreach($barangs as $b)
                        <option value="{{ $b->id }}"
                            data-nama="{{ $b->nama_barang }}"
                            data-kode="{{ $b->kode_barang }}"
                            data-kategori="{{ $b->kategori->nama_kategori ?? '-' }}"
                            data-satuan="{{ $b->satuan }}"
                            data-stok="{{ $b->stok_saat_ini }}"
                            data-min="{{ $b->stok_minimum }}"
                            data-kritis="{{ in_array($b->id, $kritisIds) ? '1' : '0' }}"
                            data-harga="{{ (float) $b->harga_beli_terakhir ?: $b->harga_beli }}"
                            data-harga-jual="{{ (float) $b->harga_jual_terakhir ?: $b->harga_jual }}"
                            data-supplier-id="{{ $b->preferred_supplier_id }}"
                            data-supplier-nama="{{ $b->preferredSupplier->nama_supplier ?? '' }}"
                            data-supplier-telp="{{ $b->preferredSupplier->telepon ?? '' }}">
                            {{ $b->nama_barang }} ({{ $b->satuan }}) — stok {{ $b->stok_saat_ini }}
                            @if($b->preferredSupplier) · beli ke: {{ $b->preferredSupplier->nama_supplier }} @endif
                        </option>
                        @endforeach
                        </select>
                        <div class="mt-2">
                            <label class="small text-muted mb-1 d-block">
                                <i class="bi bi-mouse2 me-1"></i><b>Pesan otomatis</b> — klik barang di daftar untuk langsung memasukkannya ke pesanan:
                            </label>
                            <div id="daftarPilihCepat" class="border rounded-3 p-1" style="max-height:240px; overflow-y:auto;">
                                @foreach($barangs as $i => $b)
                                <button type="button" class="btn btn-sm btn-outline-secondary baris-pilih-cepat d-flex align-items-center justify-content-between w-100 text-start mb-1"
                                        data-opt-idx="{{ $i + 1 }}"
                                        title="Klik untuk memesan {{ $b->nama_barang }}">
                                    <span>
                                        <i class="bi bi-plus-circle me-1"></i>
                                        {{ $b->nama_barang }}
                                        <small class="text-muted">(stok {{ $b->stok_saat_ini }} {{ $b->satuan }})</small>
                                    </span>
                                    @if(in_array($b->id, $kritisIds))
                                    <span class="badge bg-danger">KRITIS</span>
                                    @endif
                                </button>
                                @endforeach
                            </div>
                            <small class="text-muted">Klik barang → langsung masuk pesanan (klik lagi untuk menghapus). Barang <span class="badge bg-danger">KRITIS</span> otomatis dipesan 1× stok minimum.</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="tabelItems">
                            <thead class="table-light">
                                <tr>
                                    <th>Barang</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-center">Stok<br><small>Kini/Min</small></th>
                                    <th>Beli dari (Supplier)</th>
                                    <th class="text-center" style="width:90px;">Jumlah</th>
                                    <th class="text-end">Harga Jual<br><small class="text-muted fw-normal">(master)</small></th>
                                    <th class="text-end">Harga Beli<br><small class="text-muted fw-normal">(estimasi)</small></th>
                                    <th class="text-end">Subtotal<br><small class="text-muted fw-normal">(estimasi)</small></th>
                                    <th width="40"></th>
                                </tr>
                            </thead>
                            <tbody id="itemList"></tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="7" class="text-end fw-bold">
                                        Total (Estimasi) <span class="text-muted fw-normal" id="ringkasanQty"></span>
                                    </td>
                                    <td class="text-end fw-bold" id="totalPO">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="alert alert-info py-2 small mt-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Harga &amp; subtotal di atas adalah <strong>estimasi</strong>. Harga asli
                        dikonfirmasi ulang saat barang diterima (sesuai nota supplier).
                    </div>
                    <div id="peringatanSupplier" class="alert alert-info py-2 small mt-2 mb-0 d-none">
                        <i class="bi bi-diagram-3 me-1"></i>
                        Item berasal dari <strong>lebih dari 1 supplier</strong> — sistem otomatis membuat
                        <strong>permintaan terpisah per supplier</strong> (1 PO per supplier) saat Anda kirim.
                    </div>
                    @if($errors->any())
                    <div class="alert alert-danger py-2 small mt-2 mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        <ul class="mb-0 ps-3 mt-1">
                            @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ INFORMASI PO ═══ --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-1 text-primary"></i>Informasi PO</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nomor PO (otomatis)</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $noPo }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tanggal PO</label>
                        <input type="date" name="tanggal_po" class="form-control form-control-sm"
                            value="{{ old('tanggal_po', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Estimasi Barang Datang <span class="text-muted fw-normal">(otomatis)</span></label>
                        <input type="date" name="estimasi_datang" class="form-control form-control-sm"
                            value="{{ old('estimasi_datang', now()->addDays(7)->toDateString()) }}">
                        <small class="text-muted">Terisi otomatis (tanggal PO + 7 hari), bisa diubah.</small>
                        @error('estimasi_datang')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Supplier Utama (fallback)</label>
                        <select name="supplier_id" id="selectSupplier" class="form-select form-select-sm"
                            onchange="updateKontakSupplier()">
                            <option value="">-- Otomatis per barang --</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_supplier }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Tiap item memakai supplier pilihannya sendiri. Pilihan ini hanya
                            dipakai untuk item yang belum memilih supplier.</small>
                        @error('supplier_id')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror

                        {{-- Kontak supplier terpilih (live) --}}
                        <div id="kontakSupplier" class="alert alert-success py-2 small mt-2 mb-0 d-none">
                            <i class="bi bi-shop me-1"></i><strong id="kontakNama"></strong>
                            <div class="mt-1">
                                <i class="bi bi-telephone me-1"></i><a id="kontakTelp" href="#" class="text-success"></a>
                                <div class="text-muted"><i class="bi bi-geo-alt me-1"></i><span id="kontakAlamat"></span></div>
                                <div class="text-muted"><i class="bi bi-envelope me-1"></i><span id="kontakEmail"></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Catatan</label>
                        <textarea name="catatan" class="form-control form-control-sm" rows="3">{{ old('catatan') }}</textarea>
                    </div>
                </div>
            </div>

            @if(auth()->user()->isGudang())
            <div class="d-flex flex-column gap-2 mt-3">
                <button type="submit" name="action" value="ajukan" class="btn btn-primary"
                    onclick="return confirm('Kirim permintaan ini ke admin/owner?')">
                    <i class="bi bi-send me-1"></i> Kirim Permintaan ke Admin
                </button>
                <button type="submit" name="action" value="draft" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-save me-1"></i> Simpan Draft (belum dikirim)
                </button>
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Permintaan dikirim ke admin/owner untuk disetujui.</small>
            </div>
            @endif
        </div>
    </div>
</form>

@push('scripts')
@php
    $supplierJson = $suppliers->map(function ($s) {
        return ['id' => (int) $s->id, 'nama' => $s->nama_supplier, 'telp' => $s->telepon ?? '', 'alamat' => $s->alamat ?? '', 'email' => $s->email ?? ''];
    })->values();
@endphp
<script>
var items = [];
var prefillIds = @json($prefill);
var suppliers = @json($supplierJson);
var supplierMap = {};
suppliers.forEach(function (s) { supplierMap[s.id] = s; });

// ── Pencarian barang (ketik manual: nama / kode / kategori) ──
var daftarBarang = [];
var pilihIndexHasil = -1;

function muatDaftarBarang() {
    var select = document.getElementById('pilihBarang');
    daftarBarang = [];
    for (var i = 0; i < select.options.length; i++) {
        var o = select.options[i];
        if (!o.value) continue;
        daftarBarang.push({
            index: i,
            nama: o.getAttribute('data-nama') || o.text,
            kode: o.getAttribute('data-kode') || '',
            kategori: o.getAttribute('data-kategori') || ''
        });
    }
}

function renderHasilPencarian(q) {
    var list = document.getElementById('hasilPencarian');
    q = (q || '').toLowerCase();
    var hasil = [];
    if (q) {
        for (var i = 0; i < daftarBarang.length && hasil.length < 50; i++) {
            var b = daftarBarang[i];
            if ((b.nama + ' ' + b.kode + ' ' + b.kategori).toLowerCase().indexOf(q) !== -1) hasil.push(b);
        }
    }
    pilihIndexHasil = -1;
    if (!q) { list.classList.add('d-none'); list.innerHTML = ''; return; }
    if (hasil.length === 0) {
        list.innerHTML = '<div class="list-group-item text-muted small text-center py-2">Tidak ada barang yang cocok dengan "<b>' + q + '</b>"</div>';
        list.classList.remove('d-none');
        return;
    }
    var html = '';
    for (var j = 0; j < hasil.length; j++) {
        var it = hasil[j];
        html += '<button type="button" class="list-group-item list-group-item-action text-start small" data-barang-idx="' + it.index + '">'
             + '<div class="fw-semibold">' + it.nama + ' <code class="small text-muted">' + it.kode + '</code></div>'
             + '<div class="text-muted" style="font-size:.72rem">' + it.kategori + '</div>'
             + '</button>';
    }
    list.innerHTML = html;
    list.classList.remove('d-none');
}

function pilihHasil(barangIdx) {
    var select = document.getElementById('pilihBarang');
    select.selectedIndex = barangIdx;
    var inp = document.getElementById('pencarianBarang');
    inp.value = select.options[barangIdx].getAttribute('data-nama');
    document.getElementById('hasilPencarian').classList.add('d-none');
    inp.focus();
}

function sorotHasil(items) {
    for (var i = 0; i < items.length; i++) {
        items[i].classList.toggle('active', i === pilihIndexHasil);
    }
}

var inputCari = document.getElementById('pencarianBarang');
inputCari.addEventListener('input', function () {
    // Ketik ulang tanpa memilih hasil -> batalkan pilihan tersembunyi (hindari item basi)
    var select = document.getElementById('pilihBarang');
    if (select.selectedIndex > 0 && select.options[select.selectedIndex].getAttribute('data-nama') !== this.value) {
        select.selectedIndex = 0;
    }
    renderHasilPencarian(this.value);
});
inputCari.addEventListener('keydown', function (e) {
    var list = document.getElementById('hasilPencarian');

    // Enter: jangan sampai men-submit form PO — langsung masukkan barang terpilih ke pesanan
    if (e.key === 'Enter') {
        e.preventDefault();
        if (list.classList.contains('d-none')) return;
        var itemsEnter = list.querySelectorAll('button[data-barang-idx]');
        if (pilihIndexHasil >= 0 && itemsEnter[pilihIndexHasil]) {
            pilihHasil(parseInt(itemsEnter[pilihIndexHasil].getAttribute('data-barang-idx'), 10));
            toggleItemDariSelect(false);
        } else {
            list.classList.add('d-none');
        }
        return;
    }

    if (list.classList.contains('d-none')) return;
    var items = list.querySelectorAll('button[data-barang-idx]');
    if (items.length === 0) return;
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        var n = items.length;
        pilihIndexHasil = e.key === 'ArrowDown'
            ? (pilihIndexHasil + 1) % n
            : (pilihIndexHasil - 1 + n) % n;
        sorotHasil(items);
    } else if (e.key === 'Escape') {
        list.classList.add('d-none');
    }
});
inputCari.addEventListener('blur', function () {
    setTimeout(function () { document.getElementById('hasilPencarian').classList.add('d-none'); }, 150);
});
document.getElementById('hasilPencarian').addEventListener('mousedown', function (e) {
    var btn = e.target.closest ? e.target.closest('button[data-barang-idx]') : null;
    if (!btn) return;
    pilihHasil(parseInt(btn.getAttribute('data-barang-idx'), 10));
    toggleItemDariSelect(false);
});

// ── Muat otomatis barang dari notifikasi stok kritis ──
document.addEventListener('DOMContentLoaded', function () {
    muatDaftarBarang();
    prefillIds.forEach(function (id) {
        var select = document.getElementById('pilihBarang');
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value == id) {
                select.selectedIndex = i;
                tambahItem();
                break;
            }
        }
    });
    if (items.length > 0) {
        document.getElementById('pencarianBarang').value = items[items.length - 1].nama;
    }
    updateKontakSupplier();
});

function tambahItem(qtyDefault, noFocus) {
    var select = document.getElementById('pilihBarang');
    var option = select.options[select.selectedIndex];

    if (!option.value) {
        alert('Pilih barang terlebih dahulu.');
        return;
    }

    var id = option.value;
    qtyDefault = qtyDefault || 1;

    // Barang sama -> naikkan jumlah
    for (var i = 0; i < items.length; i++) {
        if (items[i].id === id) {
            items[i].jumlah += qtyDefault;
            renderItems();
            resetSelect(noFocus);
            return;
        }
    }

    var supId = option.getAttribute('data-supplier-id') || '';
    items.push({
        id: id,
        nama: option.getAttribute('data-nama') || '',
        kategori: option.getAttribute('data-kategori') || '-',
        satuan: option.getAttribute('data-satuan') || '-',
        stok: option.getAttribute('data-stok') || '0',
        min: option.getAttribute('data-min') || '0',
        harga: parseFloat(option.getAttribute('data-harga')) || 0,
        hargaJual: parseFloat(option.getAttribute('data-harga-jual')) || 0,
        supplierId: supId,
        supplierNama: option.getAttribute('data-supplier-nama') || '',
        supplierTelp: option.getAttribute('data-supplier-telp') || '',
        jumlah: qtyDefault
    });
    renderItems();
    resetSelect(noFocus);

    // Auto-isi supplier PO dari supplier barang pertama (jika belum dipilih)
    var selectSup = document.getElementById('selectSupplier');
    if (supId && !selectSup.value) {
        selectSup.value = supId;
        updateKontakSupplier();
    }
}

// ── Pesan otomatis: toggle barang (klik → masuk pesanan, klik lagi → hapus) ──
function toggleItemDariSelect(noFocus) {
    var select = document.getElementById('pilihBarang');
    var option = select.options[select.selectedIndex];
    if (!option || !option.value) {
        alert('Pilih barang terlebih dahulu.');
        return;
    }
    var id = option.value;

    // Sudah ada di pesanan → hapus
    for (var i = 0; i < items.length; i++) {
        if (items[i].id === id) {
            items.splice(i, 1);
            renderItems();
            resetSelect(noFocus);
            return;
        }
    }

    // Belum ada → tambah. Barang kritis otomatis qty = stok minimum, lainnya 1
    var kritis = option.getAttribute('data-kritis') === '1';
    var min = parseInt(option.getAttribute('data-min'), 10) || 1;
    tambahItem(kritis ? min : 1, noFocus);
}

// Klik badge barang kritis (bagian atas form)
function tambahItemKritis(btn) {
    var select = document.getElementById('pilihBarang');
    select.selectedIndex = parseInt(btn.getAttribute('data-opt-idx'), 10);
    toggleItemDariSelect(true);
}

// Sinkronkan tampilan tombol kritis & daftar pesan otomatis dengan isi pesanan
function sinkronBadgeKritis() {
    var select = document.getElementById('pilihBarang');
    var btns = document.querySelectorAll('.badge-kritis-pilih, .baris-pilih-cepat');
    if (!btns.length) return;
    for (var i = 0; i < btns.length; i++) {
        var opt = select.options[parseInt(btns[i].getAttribute('data-opt-idx'), 10)];
        var ada = opt && items.some(function (it) { return it.id === opt.value; });
        btns[i].classList.toggle('btn-success', !!ada);
        btns[i].classList.toggle('text-white', !!ada);
        if (btns[i].classList.contains('baris-pilih-cepat')) {
            btns[i].classList.toggle('btn-outline-secondary', !ada);
        } else {
            btns[i].classList.toggle('btn-outline-danger', !ada);
        }
        var ic = btns[i].querySelector('i');
        if (ic) ic.className = ada ? 'bi bi-check-circle me-1' : 'bi bi-plus-circle me-1';
    }
}

// Klik tombol barang kritis (event delegation)
var daftarKritis = document.getElementById('daftarBarangKritis');
if (daftarKritis) {
    daftarKritis.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.badge-kritis-pilih') : null;
        if (btn) tambahItemKritis(btn);
    });
}

// Klik daftar pesan otomatis (semua barang)
var daftarCepat = document.getElementById('daftarPilihCepat');
if (daftarCepat) {
    daftarCepat.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.baris-pilih-cepat') : null;
        if (!btn) return;
        var select = document.getElementById('pilihBarang');
        select.selectedIndex = parseInt(btn.getAttribute('data-opt-idx'), 10);
        toggleItemDariSelect(true);
    });
}

function resetSelect(noFocus) {
    var inp = document.getElementById('pencarianBarang');
    if (inp) inp.value = '';
    document.getElementById('pilihBarang').selectedIndex = 0;
    document.getElementById('hasilPencarian').classList.add('d-none');
    if (inp && !noFocus) inp.focus();
}

// Tampilkan kontak supplier yang dipilih di header PO
function updateKontakSupplier() {
    var box = document.getElementById('kontakSupplier');
    var val = document.getElementById('selectSupplier').value;
    if (!val || !supplierMap[val]) { box.classList.add('d-none'); return; }
    var s = supplierMap[val];
    document.getElementById('kontakNama').textContent = s.nama;
    document.getElementById('kontakTelp').textContent = s.telp || '-';
    document.getElementById('kontakTelp').href = s.telp ? 'tel:' + s.telp : '#';
    document.getElementById('kontakAlamat').textContent = s.alamat || '-';
    document.getElementById('kontakEmail').textContent = s.email || '-';
    box.classList.remove('d-none');
}

// Opsi supplier untuk dropdown per item (dari daftar supplier yang ada)
function supplierOptions(item) {
    var html = '<option value="">-- Pilih Supplier --</option>';
    for (var i = 0; i < suppliers.length; i++) {
        var s = suppliers[i];
        var sel = item.supplierId && String(item.supplierId) === String(s.id) ? ' selected' : '';
        html += '<option value="' + s.id + '"' + sel + '>' + s.nama + '</option>';
    }
    return html;
}

// Ganti supplier item -> sinkronkan juga supplier PO di header
function ubahSupplierItem(idx, supId) {
    if (!items[idx]) return;
    items[idx].supplierId = supId;
    var s = supplierMap[supId];
    items[idx].supplierNama = s ? s.nama : '';
    items[idx].supplierTelp = s ? (s.telp || '') : '';
    updatePeringatanSupplier();

    var selectSup = document.getElementById('selectSupplier');
    if (selectSup && supId) {
        selectSup.value = supId;
        updateKontakSupplier();
    }
}

// Peringatan bila item berasal dari lebih dari 1 supplier
function updatePeringatanSupplier() {
    var supplierSet = {};
    items.forEach(function (it) { if (it.supplierId) supplierSet[it.supplierId] = true; });
    document.getElementById('peringatanSupplier').classList.toggle('d-none', Object.keys(supplierSet).length <= 1);
}

function renderItems() {
    var tbody = document.getElementById('itemList');
    var total = 0, totalQty = 0;

    if (items.length === 0) {
        tbody.innerHTML = '<tr id="barisKosong"><td colspan="9" class="text-center text-muted py-3">' +
            '<i class="bi bi-inbox fs-4 d-block mb-1"></i>Belum ada item. Klik barang kritis atau pilih barang dari daftar pesan otomatis di atas.</td></tr>';
        document.getElementById('totalPO').textContent = 'Rp 0';
        document.getElementById('badgeJmlItem').textContent = '0 barang';
        document.getElementById('ringkasanQty').textContent = '';
        document.getElementById('peringatanSupplier').classList.add('d-none');
        sinkronBadgeKritis();
        return;
    }

    updatePeringatanSupplier();

    var html = '';
    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var subtotal = item.harga * item.jumlah;
        total += subtotal;
        totalQty += item.jumlah;
        var kritis = parseInt(item.stok, 10) <= parseInt(item.min, 10);

        html += '<tr>';
        html += '<td><small class="fw-semibold">' + item.nama + '</small><br><small class="text-muted">' + item.kategori + '</small></td>';
        html += '<td class="text-center"><small>' + item.satuan + '</small></td>';
        html += '<td class="text-center"><small>' + item.stok + ' / ' + item.min + '</small>' +
                (kritis ? ' <span class="badge bg-danger">KRITIS</span>' : '') + '</td>';
        html += '<td><select name="items[' + i + '][supplier_id]" data-idx="' + i + '" class="form-select form-select-sm input-supplier">' + supplierOptions(item) + '</select></td>';
        html += '<input type="hidden" name="items[' + i + '][barang_id]" value="' + item.id + '">';
        html += '<td><input type="number" name="items[' + i + '][jumlah]" data-idx="' + i + '" value="' + item.jumlah +
                '" min="1" class="form-control form-control-sm text-center input-qty"></td>';
        html += '<td class="text-end"><small class="text-muted">Rp ' + formatRp(item.hargaJual) + '</small></td>';
        html += '<td><input type="number" name="items[' + i + '][harga_beli]" data-idx="' + i + '" value="' + item.harga +
                '" min="0" class="form-control form-control-sm text-end input-harga"></td>';
        html += '<td class="text-end subtotal-cell"><small>Rp ' + formatRp(subtotal) + '</small></td>';
        html += '<td><button type="button" class="btn btn-sm btn-danger py-0 btn-hapus" data-idx="' + i + '"><i class="bi bi-trash"></i></button></td>';
        html += '</tr>';
    }

    tbody.innerHTML = html;
    document.getElementById('totalPO').textContent = 'Rp ' + formatRp(total);
    document.getElementById('badgeJmlItem').textContent = items.length + ' barang';
    document.getElementById('ringkasanQty').textContent = '· ' + totalQty + ' item';
    sinkronBadgeKritis();
}

// Edit jumlah/harga tanpa kehilangan fokus
document.getElementById('itemList').addEventListener('input', function (e) {
    var t = e.target;
    if (!t.matches('.input-qty, .input-harga')) return;
    var idx = parseInt(t.getAttribute('data-idx'), 10);
    if (isNaN(idx) || !items[idx]) return;

    if (t.classList.contains('input-qty')) {
        items[idx].jumlah = parseInt(t.value) || 1;
        t.value = items[idx].jumlah;
    } else {
        items[idx].harga = parseFloat(t.value) || 0;
        t.value = items[idx].harga;
    }

    var tr = t.closest('tr');
    tr.querySelector('.subtotal-cell').innerHTML = '<small>Rp ' + formatRp(items[idx].harga * items[idx].jumlah) + '</small>';

    var total = 0, totalQty = 0;
    items.forEach(function (it) { total += it.harga * it.jumlah; totalQty += it.jumlah; });
    document.getElementById('totalPO').textContent = 'Rp ' + formatRp(total);
    document.getElementById('ringkasanQty').textContent = '· ' + totalQty + ' item';
});

// Ganti supplier per item (tanpa kehilangan fokus)
document.getElementById('itemList').addEventListener('change', function (e) {
    var t = e.target;
    if (!t.matches('.input-supplier')) return;
    var idx = parseInt(t.getAttribute('data-idx'), 10);
    if (isNaN(idx) || !items[idx]) return;
    ubahSupplierItem(idx, t.value);
});

// Hapus item
document.getElementById('itemList').addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-hapus');
    if (!btn) return;
    var idx = parseInt(btn.getAttribute('data-idx'), 10);
    if (isNaN(idx)) return;
    items.splice(idx, 1);
    renderItems();
});

// Validasi sebelum submit
document.getElementById('formPO').addEventListener('submit', function (e) {
    if (items.length === 0) {
        e.preventDefault();
        alert('Tambahkan minimal 1 barang terlebih dahulu.');
    }
});

function formatRp(n) {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
</script>
@endpush
@endsection
