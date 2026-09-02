@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-1">Riwayat Transaksi</h5>
        <span class="badge bg-primary-subtle text-primary-emphasis">
            <i class="bi bi-calendar3 me-1"></i> Periode: {{ $labelPeriode }}
        </span>
    </div>
    <a href="{{ route('pos.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
    </a>
</div>

{{-- Filter: cari invoice / tanggal / bulan / tahun --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label fw-semibold small mb-1">Cari Invoice / Pelanggan</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="No. invoice atau nama pelanggan..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Tanggal</label>
                <input type="date" name="tanggal" class="form-control form-control-sm"
                    value="{{ request('tanggal') }}">
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Bulan</label>
                <select name="bulan" class="form-select form-select-sm">
                    <option value="">Semua Bulan</option>
                    @foreach($bulanList as $n => $nama)
                    <option value="{{ $n }}" {{ request('bulan') == $n ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Tahun</label>
                <select name="tahun" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunTersedia as $th)
                    <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <a href="{{ route('pos.riwayat') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
        <small class="text-muted mt-1 d-block">
            <i class="bi bi-info-circle me-1"></i>Isi tanggal untuk mencari satu hari tertentu, atau gunakan bulan & tahun untuk satu periode. Kosongkan semua untuk menampilkan transaksi hari ini.
        </small>
    </div>
</div>

{{-- Ringkasan periode --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="brand-logo" style="width:42px;height:42px;font-size:1rem;">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Transaksi ({{ $labelPeriode }})</div>
                    <div class="fw-bold fs-5">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="brand-logo" style="width:42px;height:42px;font-size:1rem;background:linear-gradient(135deg,#10b981,#34d399);">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Penjualan ({{ $labelPeriode }})</div>
                    <div class="fw-bold fs-5 text-success">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Invoice</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Metode</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end">Kembali</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $t)
                    <tr>
                        <td><code>{{ $t->no_invoice ?? '-' }}</code></td>
                        <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $t->nama_pelanggan ?? '-' }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($t->metode_bayar == 'kredit')
                                <span class="badge bg-warning text-dark small">Kredit</span>
                            @else
                                <span class="badge bg-success small">Tunai</span>
                            @endif
                        </td>
                        <td class="text-end">Rp {{ number_format($t->bayar, 0, ',', '.') }}</td>
                        <td class="text-end {{ $t->kembalian > 0 ? 'text-success' : '' }}">
                            Rp {{ number_format($t->kembalian, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('pos.struk', $t->id) }}" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-receipt"></i>
                            </a>
                            <a href="{{ route('retur.form', $t->id) }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-arrow-return-left"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            @if(request('search'))
                                <i class="bi bi-search-heart fs-2 d-block mb-2 text-danger"></i>
                                <div class="fw-semibold text-danger mb-1">Transaksi Tidak Ditemukan</div>
                                <div class="text-muted small">
                                    Tidak ada transaksi dengan invoice/pelanggan "{{ request('search') }}"{{ request('tanggal') ? ' pada tanggal ' . \Carbon\Carbon::parse(request('tanggal'))->format('d/m/Y') : '' }}.
                                </div>
                            @else
                                <i class="bi bi-inbox fs-4 d-block mb-1 text-muted"></i>
                                Belum ada transaksi pada periode ini.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transaksis->hasPages())
    <div class="card-footer bg-white">
        {{ $transaksis->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
