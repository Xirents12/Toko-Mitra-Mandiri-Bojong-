@extends('layouts.app')
@section('title','Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-700 mb-0" style="font-weight:700">Dashboard</h4>
        <p class="text-muted small mb-0">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
    <span class="badge bg-light text-dark border">{{ now()->translatedFormat('l, d F Y') }}</span>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card card h-100" style="background:linear-gradient(135deg,#1e40af,#3b82f6)">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-1 small opacity-75">Total Barang</p>
                        <h3 class="fw-700 mb-0" style="font-weight:700">{{ $totalBarang }}</h3>
                    </div>
                    <i class="bi bi-box-seam opacity-50 fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card card h-100" style="background:linear-gradient(135deg,#dc2626,#f87171)">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-1 small opacity-75">Stok Kritis</p>
                        <h3 class="fw-700 mb-0" style="font-weight:700">{{ $stokKritis }}</h3>
                    </div>
                    <i class="bi bi-exclamation-triangle opacity-50 fs-1"></i>
                </div>
                @if($stokKritis > 0)
                <a href="{{ route('laporan.stok-kritis') }}" class="text-white text-decoration-underline small">Lihat detail</a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card card h-100" style="background:linear-gradient(135deg,#059669,#34d399)">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-1 small opacity-75">Penjualan Hari Ini</p>
                        <h3 class="fw-700 mb-0" style="font-weight:700">Rp {{ number_format($penjualanHariIni ?? 0,0,',','.') }}</h3>
                    </div>
                    <i class="bi bi-cart-check opacity-50 fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card card h-100" style="background:linear-gradient(135deg,#d97706,#fbbf24)">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-1 small opacity-75">Piutang Aktif</p>
                        <h3 class="fw-700 mb-0" style="font-weight:700">Rp {{ number_format($totalPiutangAktif ?? 0,0,',','.') }}</h3>
                    </div>
                    <i class="bi bi-credit-card opacity-50 fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards Row 2 --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card card h-100 border-start border-4 border-primary">
            <div class="card-body">
                <p class="mb-0 small text-muted">Stok Masuk Bulan Ini</p>
                <h4 class="fw-bold mb-0">{{ $masukBulanIni }} <small class="text-muted fw-normal">transaksi</small></h4>
            </div>
        </div>
    </div>
    @if(!auth()->user()->isKasir())
    <div class="col-6 col-md-3">
        <div class="stat-card card h-100 border-start border-4 border-warning">
            <div class="card-body">
                <p class="mb-0 small text-muted">PO Belum Selesai</p>
                <h4 class="fw-bold mb-0 text-warning">{{ $poPending ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card card h-100 border-start border-4 border-warning">
            <div class="card-body">
                <p class="mb-0 small text-muted">Stok Overstok</p>
                <h4 class="fw-bold mb-0 text-warning">{{ $stokOverstock ?? 0 }}</h4>
                @if($stokOverstock > 0)
                <a href="{{ route('laporan.overstok') }}" class="small text-warning text-decoration-underline">Lihat detail</a>
                @endif
            </div>
        </div>
    </div>
    @endif
    <div class="col-6 col-md-3">
        <div class="stat-card card h-100 border-start border-4 border-info">
            <div class="card-body">
                <p class="mb-0 small text-muted">Total Kategori</p>
                <h4 class="fw-bold mb-0">{{ $totalKategori }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Grafik --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="table-card p-3">
            <h6 class="fw-600 mb-1"><i class="bi bi-graph-up-arrow text-success me-2"></i>Penjualan 7 Hari Terakhir</h6>
            <canvas id="grafikPenjualan" height="110"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="table-card p-3">
            <h6 class="fw-600 mb-1"><i class="bi bi-arrow-left-right text-primary me-2"></i>Transaksi Stok 7 Hari</h6>
            <canvas id="grafikStok" height="110"></canvas>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Stok Kritis Alert --}}
    @if($barangKritis->count() > 0)
    <div class="col-12">
        <div class="table-card p-0 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 border-bottom bg-danger bg-opacity-10">
                <h6 class="mb-0 fw-600 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Peringatan Stok Kritis</h6>
                <div class="d-flex gap-1 flex-wrap">
                    @if(auth()->user()->isGudang())
                    <form action="{{ route('purchase-order.auto-kritis') }}" method="POST" class="d-inline-block"
                          onsubmit="return confirm('Buat permintaan dari SEMUA barang stok kritis? Permintaan dibuat per supplier dan dikirim ke admin/owner untuk disetujui.')">
                        @csrf
                        <button class="btn btn-sm btn-danger">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Pesan Barang Kritis (Semua)
                        </button>
                    </form>
                    <form action="{{ route('purchase-order.auto-kritis') }}" method="POST" class="d-inline-block" id="formPesanTerpilih">
                        @csrf
                        <input type="hidden" name="barang_ids" id="barangIdsTerpilih" value="">
                        <button type="submit" id="btnPesanTerpilih" class="btn btn-sm btn-outline-danger" disabled
                                onclick="return konfirmasiPesanTerpilih(event)">
                            <i class="bi bi-check2-square me-1"></i> Pesan Terpilih (<span id="jmlTerpilih">0</span>)
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('laporan.stok-kritis') }}" class="btn btn-sm btn-outline-danger">Lihat Semua</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            @if(auth()->user()->isGudang())
                            <th class="text-center" style="width:42px">
                                <input type="checkbox" class="form-check-input" id="pilihSemua" title="Pilih semua barang">
                            </th>
                            @endif
                            <th>Kode</th><th>Nama Barang</th><th>Stok</th><th>Min</th><th>Satuan</th><th>Supplier Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangKritis as $b)
                        <tr>
                            @if(auth()->user()->isGudang())
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input check-item" value="{{ $b->id }}"
                                       data-nama="{{ $b->nama_barang }}">
                            </td>
                            @endif
                            <td class="text-muted small">{{ $b->kode_barang }}</td>
                            <td><a href="{{ route('barang.show', $b) }}">{{ $b->nama_barang }}</a></td>
                            <td><span class="badge bg-danger">{{ $b->stok_saat_ini }}</span></td>
                            <td>{{ $b->stok_minimum }}</td>
                            <td>{{ $b->satuan }}</td>
                            <td>
                                @if($b->preferredSupplier)
                                    <span class="small">{{ $b->preferredSupplier->nama_supplier }}</span>
                                @elseif($b->supplier_rekomendasi)
                                    <span class="small text-muted">{{ $b->supplier_rekomendasi->nama_supplier }}</span>
                                @else
                                    <span class="small text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if(auth()->user()->isGudang())
                <div class="px-3 py-2 small text-muted border-top">
                    <i class="bi bi-info-circle me-1"></i>
                    Hanya 10 barang paling kritis yang ditampilkan di sini. Gunakan
                    <a href="{{ route('laporan.stok-kritis') }}">Laporan Stok Kritis</a> untuk memilih dari semua barang kritis.
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Peringatan Stok Overstok --}}
    @php
        $barangOverstok = \App\Models\Barang::with('kategori')
            ->where('is_active', true)
            ->whereRaw('stok_saat_ini >= stok_maksimum')
            ->where('stok_maksimum', '>', 0)
            ->orderByDesc('stok_saat_ini')
            ->limit(10)->get();
    @endphp
    @if($barangOverstok->count() > 0)
    <div class="col-12">
        <div class="table-card p-0 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom bg-warning bg-opacity-10">
                <h6 class="mb-0 fw-600 text-warning"><i class="bi bi-exclamation-diamond-fill me-2"></i>Peringatan Stok Overstok</h6>
                <a href="{{ route('laporan.overstok') }}" class="btn btn-sm btn-outline-warning">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th><th>Nama Barang</th><th>Stok</th><th>Maks</th><th>Kelebihan</th><th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangOverstok as $b)
                        @php $kelebihan = $b->stok_saat_ini - $b->stok_maksimum; @endphp
                        <tr>
                            <td class="text-muted small">{{ $b->kode_barang }}</td>
                            <td><a href="{{ route('barang.show', $b) }}">{{ $b->nama_barang }}</a></td>
                            <td><span class="badge bg-warning text-dark">{{ $b->stok_saat_ini }}</span></td>
                            <td>{{ $b->stok_maksimum }}</td>
                            <td><span class="badge bg-warning text-dark">+{{ $kelebihan }}</span></td>
                            <td>{{ $b->satuan }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Transaksi Kasir Hari Ini --}}
    <div class="col-md-6">
        <div class="table-card p-0 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <h6 class="mb-0 fw-600"><i class="bi bi-receipt text-primary me-2"></i>Transaksi Kasir Hari Ini</h6>
                @if(auth()->user()->isKasir())
                    <a href="{{ route('pos.riwayat') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
                @else
                    <a href="{{ route('laporan.penjualan') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Invoice</th><th>Jam</th><th>Pelanggan</th><th class="text-end">Total</th><th class="text-center">Bayar</th></tr>
                    </thead>
                    <tbody>
                        @forelse($transaksiPosHariIni as $t)
                        <tr>
                            <td><code class="small">{{ $t->no_invoice ?? '-' }}</code></td>
                            <td class="small text-muted">{{ $t->created_at->format('H:i') }}</td>
                            <td class="small">{{ $t->nama_pelanggan ?? '-' }}</td>
                            <td class="text-end small">Rp {{ number_format($t->total_harga,0,',','.') }}</td>
                            <td class="text-center">
                                @if($t->metode_bayar == 'kredit')
                                    <span class="badge bg-warning text-dark">Kredit</span>
                                @else
                                    <span class="badge bg-success">Tunai</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted small py-3">Belum ada transaksi hari ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Transaksi Masuk Terbaru --}}
    <div class="col-md-6">
        <div class="table-card p-0 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <h6 class="mb-0 fw-600"><i class="bi bi-box-arrow-in-down text-success me-2"></i>Stok Masuk Terbaru</h6>
                <a href="{{ route('stok-masuk.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>No. Transaksi</th><th>Tanggal</th><th>Supplier</th></tr></thead>
                    <tbody>
                        @forelse($transaksiMasukTerbaru as $t)
                        <tr>
                            <td><a href="{{ route('stok-masuk.show', $t) }}" class="small">{{ $t->no_transaksi }}</a></td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($t->tanggal_masuk)->format('d/m/Y') }}</td>
                            <td class="small">{{ $t->supplier?->nama_supplier ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted small py-3">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@include('partials.po-ceklis')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
// Grafik dashboard (ES5 agar kompatibel browser lama)
document.addEventListener('DOMContentLoaded', function () {
    var labels = @json($labelsGrafik);
    var dataPenjualan = @json($dataPenjualan);
    var dataMasuk = @json($dataMasuk);
    var dataKeluar = @json($dataKeluar);

    if (window.Chart) {
        // Penjualan (bar + line)
        new Chart(document.getElementById('grafikPenjualan'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: dataPenjualan,
                    backgroundColor: 'rgba(16,185,129,.55)',
                    borderColor: '#059669',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return 'Rp ' + Number(c.parsed.y).toLocaleString('id-ID'); } } }
                },
                scales: { y: { beginAtZero: true, ticks: { callback: function (v) { return (v >= 1000 ? (v / 1000) + 'rb' : v); } } } }
            }
        });

        // Stok masuk vs keluar
        new Chart(document.getElementById('grafikStok'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Masuk', data: dataMasuk, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', fill: true, tension: .35 },
                    { label: 'Keluar', data: dataKeluar, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.10)', fill: true, tension: .35 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});
</script>
@endpush