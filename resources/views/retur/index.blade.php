@extends('layouts.app')

@section('title', 'Retur Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Retur Barang</h5>
</div>

{{-- Tab: Retur dari Pelanggan | Retur ke Supplier --}}
<ul class="nav nav-tabs mb-3" id="returTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-pelanggan" data-bs-toggle="tab"
            data-bs-target="#pane-pelanggan" type="button" role="tab"
            aria-controls="pane-pelanggan" aria-selected="true">
            <i class="bi bi-people me-1"></i> Retur dari Pelanggan
        </button>
    </li>
    {{-- Tab Retur ke Supplier hanya untuk Bagian Gudang --}}
    @if(auth()->user()->isGudang())
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-supplier" data-bs-toggle="tab"
            data-bs-target="#pane-supplier" type="button" role="tab"
            aria-controls="pane-supplier" aria-selected="false">
            <i class="bi bi-truck me-1"></i> Retur ke Supplier
            @if(isset($jumlahReturSupplier) && $jumlahReturSupplier > 0)
            <span class="badge bg-primary ms-1">{{ $jumlahReturSupplier }}</span>
            @endif
        </button>
    </li>
    @endif
</ul>

<div class="tab-content">

    {{-- ═══════════ TAB: RETUR DARI PELANGGAN ═══════════ --}}
    <div class="tab-pane fade show active" id="pane-pelanggan" role="tabpanel" aria-labelledby="tab-pelanggan">

        {{-- Cari Transaksi --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form action="{{ route('retur.cari') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Cari No. Invoice</label>
                        <input type="text" name="no_invoice" class="form-control"
                            placeholder="Masukkan nomor invoice..." required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Transaksi Terbaru --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span>Transaksi Terbaru</span>
                <small class="text-muted fw-normal">30 hari terakhir</small>
            </div>
            <div class="card-body p-0">
                @if(isset($transaksis))
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Invoice</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksis as $t)
                            @php
                                $sudahRetur   = in_array($t->no_invoice ?? (string) $t->id, $returned, true);
                                $returSelesai = in_array($t->no_invoice ?? (string) $t->id, $selesai, true);
                            @endphp
                            <tr>
                                <td><code>{{ $t->no_invoice ?? '-' }}</code></td>
                                <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $t->nama_pelanggan ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($returSelesai)
                                    <span class="badge bg-info me-1"><i class="bi bi-check2-circle me-1"></i>Retur Selesai</span>
                                    @elseif($sudahRetur)
                                    <span class="badge bg-success me-1"><i class="bi bi-check-circle me-1"></i>Sudah Diretur</span>
                                    @endif
                                    @if(!$returSelesai)
                                    <a href="{{ route('retur.form', $t->id) }}" class="btn btn-sm {{ $sudahRetur ? 'btn-outline-warning' : 'btn-warning' }}">
                                        <i class="bi bi-arrow-return-left me-1"></i> {{ $sudahRetur ? 'Retur Lagi' : 'Retur' }}
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                    Tidak ada transaksi dalam 30 hari terakhir.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @elseif(isset($transaksi))
                <div class="p-3">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Menampilkan hasil pencarian untuk invoice: <strong>{{ $transaksi->no_invoice }}</strong>
                        <a href="{{ route('retur.index') }}" class="ms-2">Reset</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sudahRetur   = in_array($transaksi->no_invoice ?? (string) $transaksi->id, $returned, true);
                                    $returSelesai = in_array($transaksi->no_invoice ?? (string) $transaksi->id, $selesai, true);
                                @endphp
                                <tr>
                                    <td><code>{{ $transaksi->no_invoice ?? '-' }}</code></td>
                                    <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $transaksi->nama_pelanggan ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if($returSelesai)
                                        <span class="badge bg-info me-1"><i class="bi bi-check2-circle me-1"></i>Retur Selesai</span>
                                        @elseif($sudahRetur)
                                        <span class="badge bg-success me-1"><i class="bi bi-check-circle me-1"></i>Sudah Diretur</span>
                                        @endif
                                        @if(!$returSelesai)
                                        <a href="{{ route('retur.form', $transaksi->id) }}" class="btn btn-sm {{ $sudahRetur ? 'btn-outline-warning' : 'btn-warning' }}">
                                            <i class="bi bi-arrow-return-left me-1"></i> {{ $sudahRetur ? 'Retur Lagi' : 'Retur' }}
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-arrow-return-left fs-4 d-block mb-1"></i>
                    Silakan cari invoice untuk melakukan retur.
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ═══════════ TAB: RETUR KE SUPPLIER (hanya Gudang) ═══════════ --}}
    @if(auth()->user()->isGudang())
    <div class="tab-pane fade" id="pane-supplier" role="tabpanel" aria-labelledby="tab-supplier">

        {{-- Ringkasan --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="brand-logo" style="width:46px;height:46px;font-size:1.1rem;">
                            <i class="bi bi-arrow-return-left"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Retur ke Supplier</div>
                            <div class="fw-bold fs-5">{{ number_format($totalTransaksi, 0, ',', '.') }} transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="brand-logo" style="width:46px;height:46px;font-size:1.1rem;background:linear-gradient(135deg,#f59e0b,#fbbf24);">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Barang Diretur</div>
                            <div class="fw-bold fs-5">{{ number_format($totalQty, 0, ',', '.') }} unit</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="brand-logo" style="width:46px;height:46px;font-size:1.1rem;background:linear-gradient(135deg,#ef4444,#f87171);">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Nilai Retur</div>
                            <div class="fw-bold fs-5">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cari Nota Stok Masuk --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form action="{{ route('retur.supplier-cari') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Cari No. Nota Stok Masuk</label>
                        <input type="text" name="no_nota" class="form-control"
                            placeholder="Masukkan nomor nota stok masuk (contoh: SM-...)" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Aksi --}}
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('retur.supplier-riwayat') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-1"></i> Riwayat Lengkap
            </a>
        </div>

        {{-- Barang dari Supplier --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span>Barang dari Supplier</span>
                <small class="text-muted fw-normal">Klik Retur — supplier otomatis dari nota terakhir</small>
            </div>
            <div class="card-body p-0">
                @if(isset($stokMasuk))
                <div class="p-3">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Menampilkan hasil pencarian untuk nota: <strong>{{ $stokMasuk->no_transaksi }}</strong>
                        <a href="{{ route('retur.index') }}" class="ms-2">Reset</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Nota</th>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th class="text-center">Jml Item</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sudahRetur   = in_array($stokMasuk->no_transaksi, $returnedNota, true);
                                    $returSelesai = in_array($stokMasuk->no_transaksi, $selesaiNota, true);
                                    $totalNota    = $stokMasuk->details->sum(fn ($d) => $d->jumlah * $d->harga_beli);
                                @endphp
                                <tr>
                                    <td><code>{{ $stokMasuk->no_transaksi }}</code></td>
                                    <td>{{ \Carbon\Carbon::parse($stokMasuk->tanggal_masuk)->format('d/m/Y') }}</td>
                                    <td>{{ $stokMasuk->supplier->nama_supplier ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $stokMasuk->details->count() }} item</span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($totalNota, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if($returSelesai)
                                        <span class="badge bg-info"><i class="bi bi-check2-circle me-1"></i>Retur Selesai</span>
                                        @elseif($sudahRetur)
                                        <span class="badge bg-success me-1"><i class="bi bi-check-circle me-1"></i>Sudah Diretur</span>
                                        @endif
                                        @if(!$returSelesai)
                                        <a href="{{ route('retur.supplier-form', $stokMasuk->id) }}" class="btn btn-sm {{ $sudahRetur ? 'btn-outline-warning' : 'btn-warning' }}">
                                            <i class="bi bi-arrow-return-left me-1"></i> {{ $sudahRetur ? 'Retur Lagi' : 'Retur' }}
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @elseif(isset($barangSuppliers))
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Barang</th>
                                <th class="text-center">Stok</th>
                                <th>Retur ke Supplier</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barangSuppliers as $i => $b)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    {{ $b->barang->nama_barang }}
                                    <br><small class="text-muted">{{ $b->barang->kode_barang }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $b->barang->stok_saat_ini }} {{ $b->barang->satuan }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning-emphasis">
                                        <i class="bi bi-truck me-1"></i>{{ $b->supplier->nama_supplier }}
                                    </span>
                                    <small class="text-muted d-block">Nota {{ $b->no_nota }} · sisa {{ $b->sisa }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('retur.supplier-form', $b->nota->id) }}" class="btn btn-warning btn-sm"
                                       title="Retur {{ $b->barang->nama_barang }} ke {{ $b->supplier->nama_supplier }}">
                                        <i class="bi bi-arrow-return-left me-1"></i> Retur
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                    Belum ada barang dari supplier yang bisa diretur.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-arrow-return-left fs-4 d-block mb-1"></i>
                    Tidak ada barang dari supplier yang bisa diretur.
                </div>
                @endif
            </div>
        </div>

    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
// Aktifkan tab "Retur ke Supplier" jika URL memuat ?tab=supplier
document.addEventListener('DOMContentLoaded', function () {
    var params = new URLSearchParams(window.location.search);
    if (params.get('tab') !== 'supplier') return;
    var btn = document.getElementById('tab-supplier');
    if (btn && typeof bootstrap !== 'undefined') {
        bootstrap.Tab.getOrCreateInstance(btn).show();
    }
});
</script>
@endpush
