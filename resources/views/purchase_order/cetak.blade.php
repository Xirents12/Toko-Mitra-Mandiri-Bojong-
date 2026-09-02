<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak PO — {{ $purchaseOrder->no_po }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; padding: 24px; color: #111; }
        .header { border-bottom: 3px solid #0d6efd; padding-bottom: 12px; margin-bottom: 16px; }
        .title-po { font-size: 20px; font-weight: 700; }
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
        <a href="{{ route('purchase-order.show', $purchaseOrder->id) }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="header d-flex justify-content-between align-items-end">
        <div>
            <div class="title-po">PURCHASE ORDER</div>
            <div class="text-muted">Toko Mitra Mandiri Bojong</div>
            <div class="small text-muted">Jl. Raya Bojong, Kab. Bandung Barat</div>
        </div>
        <div class="text-end">
            <div><strong>No. PO:</strong> <code>{{ $purchaseOrder->no_po }}</code></div>
            <div class="small"><strong>Tanggal:</strong> {{ $purchaseOrder->tanggal_po->format('d/m/Y') }}</div>
            @if($purchaseOrder->estimasi_datang)
            <div class="small"><strong>Estimasi Datang:</strong> {{ $purchaseOrder->estimasi_datang->format('d/m/Y') }}</div>
            @endif
            <div class="small"><strong>Status:</strong> {{ $purchaseOrder->status_label }}</div>
        </div>
    </div>

    <table class="table table-borderless small mb-4" style="max-width:480px;">
        <tr>
            <td style="width:120px;"><strong>Supplier</strong></td>
            <td>: {{ $purchaseOrder->supplier->nama_supplier ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Alamat</strong></td>
            <td>: {{ $purchaseOrder->supplier->alamat ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Telepon</strong></td>
            <td>: {{ $purchaseOrder->supplier->telepon ?? '-' }}</td>
        </tr>
    </table>

    <table class="table table-bordered table-sm table-detail">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Nama Barang</th>
                <th class="text-center" style="width:90px;">Qty Pesan</th>
                <th class="text-center" style="width:100px;">Satuan</th>
                <th class="text-end" style="width:150px;">Harga Beli<br><small class="text-muted">(estimasi)</small></th>
                <th class="text-end" style="width:150px;">Subtotal<br><small class="text-muted">(estimasi)</small></th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->details as $detail)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $detail->barang->nama_barang }}<br><small class="text-muted">{{ $detail->barang->kode_barang }}</small></td>
                <td class="text-center">{{ $detail->jumlah }}</td>
                <td class="text-center">{{ $detail->barang->satuan }}</td>
                <td class="text-end">Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="5" class="text-end">TOTAL (ESTIMASI)</td>
                <td class="text-end">Rp {{ number_format($purchaseOrder->total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($purchaseOrder->catatan)
    <div class="small mb-4">
        <strong>Catatan:</strong> {{ $purchaseOrder->catatan }}
    </div>
    @endif

    <div class="small text-muted mb-4">
        * Harga &amp; total di atas adalah estimasi. Harga asli dikonfirmasi sesuai nota supplier saat barang diterima.
    </div>

    <div class="footer d-flex justify-content-between">
        <div class="ttd">
            <div class="small text-muted mb-4">Dibuat Oleh,</div>
            <div class="mb-1">{{ $purchaseOrder->user->name ?? '-' }}</div>
            <div style="border-top:1px solid #000; padding-top:4px;">({{ $purchaseOrder->user->role_label ?? '' }})</div>
        </div>
        <div class="ttd">
            <div class="small text-muted mb-4">Disetujui Oleh,</div>
            <div class="mb-1">Pemilik</div>
            <div style="border-top:1px solid #000; padding-top:4px;">(____________________)</div>
        </div>
        <div class="ttd">
            <div class="small text-muted mb-4">Diterima Oleh,</div>
            <div class="mb-1">Bagian Gudang</div>
            <div style="border-top:1px solid #000; padding-top:4px;">(____________________)</div>
        </div>
    </div>

    <div class="text-center text-muted small mt-4">
        Dokumen ini dicetak dari Sistem Informasi Manajemen Stok Gudang — {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
