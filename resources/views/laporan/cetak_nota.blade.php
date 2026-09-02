<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan — {{ $transaksi->no_invoice ?? $transaksi->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; padding: 24px; color: #111; }
        .header { border-bottom: 3px solid #0d6efd; padding-bottom: 12px; margin-bottom: 16px; }
        .title-nota { font-size: 20px; font-weight: 700; }
        .table-detail th { background: #f1f5f9; }
        .meta td { padding: 2px 8px; }
        .footer { margin-top: 40px; }
        .ttd { display: inline-block; width: 200px; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-3">
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Cetak / Simpan PDF</button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="header d-flex justify-content-between align-items-end">
        <div>
            <div class="title-nota">NOTA PENJUALAN</div>
            <div class="text-muted">Toko Mitra Mandiri Bojong</div>
            <div class="small text-muted">Jl. Raya Bojong, Kab. Bandung Barat</div>
        </div>
        <div class="text-end">
            <div><strong>No. Invoice:</strong> <code>{{ $transaksi->no_invoice ?? '-' }}</code></div>
            <div class="small"><strong>Tanggal:</strong> {{ $transaksi->created_at ? $transaksi->created_at->format('d/m/Y H:i') : '-' }}</div>
            <div class="small"><strong>Kasir:</strong> {{ $transaksi->kasir_nama }}</div>
            <div class="small"><strong>Pembayaran:</strong>
                @if($transaksi->metode_bayar == 'kredit')
                    <span class="badge bg-warning text-dark">Kredit</span>
                @else
                    <span class="badge bg-success">Tunai</span>
                @endif
            </div>
        </div>
    </div>

    @if($transaksi->nama_pelanggan)
    <table class="table table-borderless small mb-4" style="max-width:480px;">
        <tr>
            <td style="width:120px;"><strong>Pelanggan</strong></td>
            <td>: {{ $transaksi->nama_pelanggan }}</td>
        </tr>
    </table>
    @endif

    <table class="table table-bordered table-sm table-detail">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Nama Barang</th>
                <th class="text-center" style="width:80px;">Qty</th>
                <th class="text-center" style="width:90px;">Satuan</th>
                <th class="text-end" style="width:130px;">Harga</th>
                <th class="text-end" style="width:140px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi->detailTransaksi as $detail)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $detail->barang->nama_barang ?? '-' }}<br><small class="text-muted">{{ $detail->barang->kode_barang ?? '' }}</small></td>
                <td class="text-center">{{ $detail->jumlah }}</td>
                <td class="text-center">{{ $detail->barang->satuan ?? '' }}</td>
                <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada detail item.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="5" class="text-end">TOTAL</td>
                <td class="text-end">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($transaksi->metode_bayar == 'tunai')
    <table class="table table-borderless table-sm small" style="max-width:340px; margin-left:auto;">
        <tr>
            <td>Bayar</td>
            <td class="text-end">Rp {{ number_format($transaksi->bayar, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold text-success">
            <td>Kembalian</td>
            <td class="text-end">Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>
    @endif

    @if($transaksi->metode_bayar == 'kredit' && $transaksi->piutang)
    <table class="table table-borderless table-sm small" style="max-width:340px; margin-left:auto;">
        <tr class="fw-bold text-warning"><td colspan="2">Informasi Kredit</td></tr>
        @if($transaksi->bayar > 0)
        <tr>
            <td>Uang Muka (DP)</td>
            <td class="text-end">Rp {{ number_format($transaksi->bayar, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td>Sisa Piutang</td>
            <td class="text-end">Rp {{ number_format($transaksi->piutang->sisa_piutang, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Cicilan</td>
            <td class="text-end">{{ $transaksi->piutang->max_cicilan }}x</td>
        </tr>
        <tr>
            <td>Tenor</td>
            <td class="text-end">{{ $transaksi->piutang->tenor_bulan }} bulan</td>
        </tr>
        <tr>
            <td>Jatuh Tempo</td>
            <td class="text-end">{{ $transaksi->piutang->tanggal_jatuh_tempo ? $transaksi->piutang->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}</td>
        </tr>
    </table>
    @endif

    <div class="footer d-flex justify-content-between">
        <div class="ttd">
            <div class="small text-muted mb-4">Hormat Kami,</div>
            <div class="mb-1">{{ $transaksi->kasir_nama }}</div>
            <div style="border-top:1px solid #000; padding-top:4px;">(Kasir)</div>
        </div>
        <div class="ttd">
            <div class="small text-muted mb-4">Penerima,</div>
            <div class="mb-1">{{ $transaksi->nama_pelanggan ?? '____________________' }}</div>
            <div style="border-top:1px solid #000; padding-top:4px;">(____________________)</div>
        </div>
    </div>

    <div class="text-center text-muted small mt-4">
        Terima kasih atas kunjungan Anda! — Dicetak {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
