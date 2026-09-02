@extends('layouts.app')

@section('title', 'Kasir')
@section('breadcrumb')
    <li class="breadcrumb-item active">Kasir</li>
@endsection

@section('content')
<div class="row g-3">

    {{-- Daftar Barang --}}
    <div class="col-md-7">
        <div class="table-card p-3">
            <h5 class="fw-bold mb-3">Pilih Barang</h5>
            <input type="text" id="searchBarang" class="form-control mb-3" placeholder="Cari barang...">
            <div class="row g-2" id="barangList">
                @foreach($barangs as $barang)
                <div class="col-6 col-lg-4 barang-item" data-nama="{{ strtolower($barang->nama_barang) }}">
                    <div class="card h-100 border"
                         style="cursor:pointer"
                         data-id="{{ $barang->id }}"
                         data-nama-barang="{{ $barang->nama_barang }}"
                         data-harga="{{ $barang->harga_jual }}"
                         data-stok="{{ $barang->stok_saat_ini }}"
                         data-satuan="{{ $barang->satuan }}"
                         onclick="tambahItemFromCard(this)">
                        <div class="card-body p-2 text-center">
                            <i class="bi bi-box-seam fs-3 text-primary"></i>
                            <p class="mb-1 small fw-bold mt-1">{{ $barang->nama_barang }}</p>
                            <p class="mb-0 text-success small fw-bold">
                                Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}
                            </p>
                            <span class="badge mt-1 bg-{{ $barang->stok_saat_ini <= $barang->stok_minimum ? 'warning' : 'success' }}">
                                Stok: {{ $barang->stok_saat_ini }} {{ $barang->satuan }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Keranjang --}}
    <div class="col-md-5">
        <div class="table-card p-3">
            <h5 class="fw-bold mb-3">Keranjang</h5>

            <div id="keranjang" class="mb-3" style="min-height:200px">
                <p class="text-muted text-center mt-5">Belum ada item</p>
            </div>

            <hr>
            <div class="mb-2">
                <label class="form-label small fw-bold">Nama Kasir</label>
                <input type="text" id="namaKasir" class="form-control form-control-sm" value="{{ auth()->user()->name }}">
            </div>

            <div class="mb-2">
                <label class="form-label small fw-bold">Nama Pelanggan</label>
                <input type="text" id="namaPelanggan" class="form-control form-control-sm" placeholder="Nama pelanggan (opsional)">
            </div>

            {{-- Metode Pembayaran --}}
            <div class="mb-2">
                <label class="form-label small fw-bold">Metode Pembayaran</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metodeBayar" id="bayarTunai" value="tunai" checked onchange="toggleMetodeBayar()">
                        <label class="form-check-label small" for="bayarTunai">
                            <i class="bi bi-cash me-1"></i> Tunai
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metodeBayar" id="bayarKredit" value="kredit" onchange="toggleMetodeBayar()">
                        <label class="form-check-label small" for="bayarKredit">
                            <i class="bi bi-credit-card me-1"></i> Kredit
                        </label>
                    </div>
                </div>
            </div>

            {{-- Opsi Kredit --}}
            <div id="opsiKredit" class="d-none mb-2">
                <label class="form-label small fw-bold">Tenor (Bulan) - Maks 3 Bulan</label>
                <select id="tenorBulan" class="form-select form-select-sm mb-2">
                    <option value="1">1 Bulan</option>
                    <option value="2">2 Bulan</option>
                    <option value="3" selected>3 Bulan</option>
                </select>
                <label class="form-label small fw-bold">Max Cicilan (max 5x)</label>
                <select id="maxCicilan" class="form-select form-select-sm">
                    <option value="1">1x cicil</option>
                    <option value="2">2x cicil</option>
                    <option value="3" selected>3x cicil</option>
                    <option value="4">4x cicil</option>
                    <option value="5">5x cicil</option>
                </select>
                <div class="mt-2">
                    <label class="form-label small fw-bold">Uang Muka (DP)</label>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="inputDp" class="form-control" placeholder="0" min="0" value="0" oninput="hitungSisaKredit()">
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Sisa Kredit</span>
                        <span id="sisaKredit" class="fw-bold text-danger">Rp 0</span>
                    </div>
                    <input type="text" id="noTelepon" class="form-control form-control-sm mb-1" placeholder="No. Telepon (opsional)">
                    <input type="text" id="alamat" class="form-control form-control-sm" placeholder="Alamat (opsional)">
                </div>
            </div>

            <div class="d-flex justify-content-between fw-bold mb-2">
                <span>Total</span>
                <span id="totalHarga">Rp 0</span>
            </div>

            <div id="tunaiSection">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Bayar</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="inputBayar" class="form-control" placeholder="0" aria-label="Nominal bayar">
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Kembalian</span>
                    <span id="kembalian" class="fw-bold text-success">Rp 0</span>
                </div>
            </div>

            <button id="btnProses" type="button" onclick="prosesTransaksi()" class="btn btn-primary w-100">
                <i class="bi bi-check-circle me-1"></i> Proses Transaksi
            </button>
        </div>
    </div>
</div>

<form id="formTransaksi" action="{{ route('pos.store') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="metode_bayar" id="formMetodeBayar" value="tunai">
    <input type="hidden" name="nama_kasir" id="formNamaKasir" value="{{ auth()->user()->name }}">
    <input type="hidden" name="nama_pelanggan" id="formNamaPelanggan">
    <input type="hidden" name="bayar" id="formBayar">
    <input type="hidden" name="dp" id="formDp" value="0">
    <input type="hidden" name="max_cicilan" id="formMaxCicilan">
    <input type="hidden" name="tenor_bulan" id="formTenorBulan">
    <input type="hidden" name="no_telepon" id="formNoTelepon">
    <input type="hidden" name="alamat" id="formAlamat">
    <div id="formItems"></div>
</form>
@endsection

@push('scripts')
<script>
var keranjang = [];

function tambahItemFromCard(el) {
    var id    = parseInt(el.getAttribute('data-id'));
    var nama  = el.getAttribute('data-nama-barang');
    var harga = parseInt(el.getAttribute('data-harga'));
    var stok  = parseInt(el.getAttribute('data-stok'));
    var satuan = el.getAttribute('data-satuan') || 'pcs';
    tambahItem(id, nama, harga, stok, satuan);
}

function tambahItem(id, nama, harga, stok, satuan) {
    var existing = null;
    for (var i = 0; i < keranjang.length; i++) {
        if (keranjang[i].id === id) { existing = keranjang[i]; break; }
    }
    if (existing) {
        if (existing.jumlah >= stok) {
            alert('Stok tidak mencukupi!');
            return;
        }
        existing.jumlah++;
    } else {
        keranjang.push({ id: id, nama: nama, harga: harga, jumlah: 1, stok: stok, satuan: satuan });
    }
    renderKeranjang();
}

function renderKeranjang() {
    var div = document.getElementById('keranjang');

    if (keranjang.length === 0) {
        div.innerHTML = '<p class="text-muted text-center mt-5">Belum ada item</p>';
        document.getElementById('totalHarga').textContent = 'Rp 0';
        document.getElementById('kembalian').textContent = 'Rp 0';
        return;
    }

    var total = 0;
    var html = '<table class="table table-sm">';
    html += '<thead><tr><th>Barang</th><th class="text-center">Qty</th><th>Satuan</th><th class="text-end">Subtotal</th><th></th></tr></thead><tbody>';

    for (var i = 0; i < keranjang.length; i++) {
        var item = keranjang[i];
        var subtotal = item.harga * item.jumlah;
        total += subtotal;

        html += '<tr>';
        html += '<td><small>' + item.nama + '</small><br><small class="text-muted">Rp ' + formatRp(item.harga) + '</small></td>';
        html += '<td class="text-center"><div class="d-flex align-items-center justify-content-center gap-1">';
        html += '<button class="btn btn-sm btn-outline-secondary py-0" onclick="ubahQty(' + i + ', -1)">-</button>';
        html += '<input type="number" title="Ketik untuk mengubah jumlah" class="form-control form-control-sm text-center fw-bold qty-input" style="width:62px" value="' + item.jumlah + '" min="1" max="' + item.stok + '" oninput="setQtyLive(' + i + ', this.value)" onchange="setQty(' + i + ', this.value)">';
        html += '<button class="btn btn-sm btn-outline-secondary py-0" onclick="ubahQty(' + i + ', 1)">+</button>';
        html += '</div></td>';
        html += '<td class="text-center"><small class="text-muted">' + (item.satuan || 'pcs') + '</small></td>';
        html += '<td class="text-end"><small data-subtotal>Rp ' + formatRp(subtotal) + '</small></td>';
        html += '<td><button class="btn btn-sm btn-danger py-0" onclick="hapusItem(' + i + ')"><i class="bi bi-trash"></i></button></td>';
        html += '</tr>';
    }

    html += '</tbody></table>';
    div.innerHTML = html;
    document.getElementById('totalHarga').textContent = 'Rp ' + formatRp(total);
    hitungKembalian();
    hitungSisaKredit();
}

function ubahQty(idx, delta) {
    keranjang[idx].jumlah += delta;
    if (keranjang[idx].jumlah <= 0) {
        keranjang.splice(idx, 1);
    } else if (keranjang[idx].jumlah > keranjang[idx].stok) {
        alert('Stok tidak mencukupi!');
        keranjang[idx].jumlah = keranjang[idx].stok;
    }
    renderKeranjang();
}

function setQtyLive(idx, value) {
    // Update total langsung saat mengetik tanpa render ulang (agar fokus tetap di input)
    var q = parseInt(value);
    if (isNaN(q) || q <= 0) return;
    if (q > keranjang[idx].stok) return;
    keranjang[idx].jumlah = q;

    // Perbarui subtotal baris itu juga agar konsisten dengan total
    var inputs = document.querySelectorAll('.qty-input');
    if (inputs[idx]) {
        var tr = inputs[idx].closest('tr');
        if (tr) {
            var subEl = tr.querySelector('[data-subtotal]');
            if (subEl) subEl.textContent = 'Rp ' + formatRp(keranjang[idx].harga * q);
        }
    }
    hitungTotalKeranjang();
}

function setQty(idx, value) {
    var q = parseInt(value);
    if (isNaN(q) || q <= 0) {
        keranjang[idx].jumlah = 1;
    } else if (q > keranjang[idx].stok) {
        alert('Stok tidak mencukupi! Maksimal ' + keranjang[idx].stok + '.');
        keranjang[idx].jumlah = keranjang[idx].stok;
    } else {
        keranjang[idx].jumlah = q;
    }
    renderKeranjang();
}

function hitungTotalKeranjang() {
    var total = 0;
    for (var i = 0; i < keranjang.length; i++) {
        total += keranjang[i].harga * keranjang[i].jumlah;
    }
    document.getElementById('totalHarga').textContent = 'Rp ' + formatRp(total);
    hitungKembalian();
    hitungSisaKredit();
}

function hitungSisaKredit() {
    var total = 0;
    for (var i = 0; i < keranjang.length; i++) {
        total += keranjang[i].harga * keranjang[i].jumlah;
    }
    var dp = parseInt(document.getElementById('inputDp').value) || 0;
    if (dp < 0) dp = 0;
    var sisa = total - dp;
    var el = document.getElementById('sisaKredit');
    if (el) {
        el.textContent = 'Rp ' + formatRp(sisa < 0 ? 0 : sisa);
        el.className = 'fw-bold ' + (sisa > 0 ? 'text-danger' : 'text-success');
    }
}

function hapusItem(idx) {
    keranjang.splice(idx, 1);
    renderKeranjang();
}

function formatRp(n) {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function hitungKembalian() {
    var total = 0;
    for (var i = 0; i < keranjang.length; i++) {
        total += keranjang[i].harga * keranjang[i].jumlah;
    }
    var bayar = parseInt(document.getElementById('inputBayar').value) || 0;
    var kembalian = bayar - total;
    var elKembalian = document.getElementById('kembalian');
    elKembalian.textContent = 'Rp ' + formatRp(kembalian < 0 ? 0 : kembalian);
    elKembalian.className = kembalian < 0 ? 'fw-bold text-danger' : 'fw-bold text-success';
}

function toggleMetodeBayar() {
    var isKredit = document.getElementById('bayarKredit').checked;
    document.getElementById('opsiKredit').classList.toggle('d-none', !isKredit);
    document.getElementById('tunaiSection').classList.toggle('d-none', isKredit);
}

function prosesTransaksi() {
    var btn = document.getElementById('btnProses');
    if (btn && btn.disabled) return; // cegah klik ganda saat sedang memproses

    if (keranjang.length === 0) {
        alert('Keranjang masih kosong!');
        return;
    }
    var total = 0;
    for (var i = 0; i < keranjang.length; i++) {
        total += keranjang[i].harga * keranjang[i].jumlah;
    }

    var isKredit = document.getElementById('bayarKredit') && document.getElementById('bayarKredit').checked;

    if (isKredit) {
        var namaPelanggan = document.getElementById('namaPelanggan').value.trim();
        if (!namaPelanggan) {
            alert('Nama pelanggan wajib diisi untuk pembayaran kredit!');
            return;
        }
        var dp = parseInt(document.getElementById('inputDp').value) || 0;
        if (dp < 0) dp = 0;
        if (dp >= total) {
            alert('Uang muka (DP) tidak boleh sama dengan atau melebihi total (Rp ' + formatRp(total) + '). Jika ingin melunasi, gunakan metode Tunai.');
            return;
        }
        document.getElementById('formBayar').value = dp;
        document.getElementById('formDp').value = dp;
    } else {
        var bayar = parseInt(document.getElementById('inputBayar').value) || 0;
        if (bayar < total) {
            alert('Nominal bayar kurang dari total!');
            return;
        }
        document.getElementById('formBayar').value = bayar;
        document.getElementById('formDp').value = 0;
    }

    document.getElementById('formMetodeBayar').value = isKredit ? 'kredit' : 'tunai';
    var namaKasir = document.getElementById('namaKasir').value.trim();
    document.getElementById('formNamaKasir').value = namaKasir || document.getElementById('formNamaKasir').value;
    document.getElementById('formNamaPelanggan').value = document.getElementById('namaPelanggan').value;
    document.getElementById('formMaxCicilan').value = isKredit ? document.getElementById('maxCicilan').value : '';
    document.getElementById('formTenorBulan').value = isKredit ? document.getElementById('tenorBulan').value : '';
    document.getElementById('formNoTelepon').value = document.getElementById('noTelepon').value;
    document.getElementById('formAlamat').value = document.getElementById('alamat').value;

    var formItems = document.getElementById('formItems');
    formItems.innerHTML = '';
    for (var i = 0; i < keranjang.length; i++) {
        var inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'items[' + i + '][id]';
        inputId.value = keranjang[i].id;
        formItems.appendChild(inputId);

        var inputJumlah = document.createElement('input');
        inputJumlah.type = 'hidden';
        inputJumlah.name = 'items[' + i + '][jumlah]';
        inputJumlah.value = keranjang[i].jumlah;
        formItems.appendChild(inputJumlah);
    }

    // Tampilkan status memproses agar user tahu transaksi sedang dijalankan
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
    }
    document.getElementById('formTransaksi').submit();
}

// Inisialisasi setelah DOM siap — aman walaupun ada elemen yang tidak ditemukan
document.addEventListener('DOMContentLoaded', function() {
    var inputBayar = document.getElementById('inputBayar');
    if (inputBayar) inputBayar.addEventListener('input', hitungKembalian);

    // Catatan: tombol Proses memakai onclick inline (lebih andal) sehingga
    // prosesTransaksi() selalu terpanggil walau pun listener gagal dipasang.

    var searchBarang = document.getElementById('searchBarang');
    if (searchBarang) searchBarang.addEventListener('input', function() {
        var q = this.value.toLowerCase();
        var items = document.querySelectorAll('.barang-item');
        for (var i = 0; i < items.length; i++) {
            items[i].style.display = items[i].getAttribute('data-nama').indexOf(q) !== -1 ? '' : 'none';
        }
    });
});
</script>
@endpush
