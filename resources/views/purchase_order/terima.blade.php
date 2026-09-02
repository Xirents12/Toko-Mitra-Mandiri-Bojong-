@extends('layouts.app')

@section('title', 'Penerimaan Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Penerimaan Barang</h5>
    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2">
    <i class="bi bi-truck fs-4"></i>
    <div>
        <strong>PO: <code>{{ $purchaseOrder->no_po }}</code></strong> — {{ $purchaseOrder->supplier->nama_supplier ?? '-' }}
        <span class="badge bg-{{ $purchaseOrder->status_color }} ms-1">{{ $purchaseOrder->status_label }}</span>
        <br><small>Masukkan jumlah barang yang benar-benar diterima, lalu <b>koreksi harga beli sesuai nota supplier</b> (harga di PO hanyalah estimasi). Stok bertambah otomatis setelah disimpan.</small>
    </div>
</div>

<form action="{{ route('purchase-order.terima-simpan', $purchaseOrder->id) }}" method="POST" id="formTerima">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Qty Dipesan</th>
                            <th class="text-center">Qty Sudah Diterima</th>
                            <th class="text-center" style="width:120px;">Qty Diterima Sekarang</th>
                            <th class="text-end" style="width:160px;">Harga Beli Aktual</th>
                            <th class="text-end" style="width:140px;">Subtotal</th>
                            <th class="text-center">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->details as $detail)
                        @php $sisa = $detail->jumlah - $detail->qty_diterima; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="fw-semibold">{{ $detail->barang->nama_barang }}</span><br>
                                <small class="text-muted">{{ $detail->barang->satuan }}</small>
                            </td>
                            <td class="text-center">{{ $detail->jumlah }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $detail->qty_diterima }}</span>
                            </td>
                            <td>
                                <input type="hidden" name="items[{{ $loop->index }}][detail_id]" value="{{ $detail->id }}">
                                <input type="number" name="items[{{ $loop->index }}][qty_diterima]"
                                    class="form-control form-control-sm text-center input-terima"
                                    data-pesan="{{ $detail->jumlah }}" data-diterima="{{ $detail->qty_diterima }}"
                                    value="0" min="0" max="{{ max($sisa, 0) }}">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $loop->index }}][harga_beli]"
                                    class="form-control form-control-sm text-end input-harga"
                                    value="{{ (float) $detail->harga_beli }}" min="0" step="any">
                            </td>
                            <td class="text-end subtotal-cell"><small>Rp 0</small></td>
                            <td class="text-center selisih-cell">
                                @if($sisa > 0)
                                <span class="badge bg-warning text-dark">{{ $sisa }}</span>
                                @else
                                <span class="badge bg-success">0</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="6" class="text-end">Total Penerimaan</td>
                            <td class="text-end" id="totalTerima">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>Jika qty diterima &lt; qty pesan, status PO menjadi <b>Diterima Sebagian</b>.
            </small>
            <div class="d-flex gap-2">
                <a href="{{ route('purchase-order.show', $purchaseOrder->id) }}" class="btn btn-outline-secondary btn-sm">Batal</a>
                <button type="submit" class="btn btn-success btn-sm" id="btnSimpanTerima"
                    onclick="return validasiTerima()">
                    <i class="bi bi-check-lg me-1"></i> Simpan Penerimaan
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function formatRp(n) {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Hitung ulang subtotal per baris + total penerimaan (qty diterima x harga aktual)
function hitungTotalTerima() {
    var total = 0;
    var rows = document.querySelectorAll('#formTerima tbody tr');
    for (var i = 0; i < rows.length; i++) {
        var tr = rows[i];
        var qty = parseInt(tr.querySelector('.input-terima').value, 10) || 0;
        var harga = parseFloat(tr.querySelector('.input-harga').value) || 0;
        var sub = qty * harga;
        tr.querySelector('.subtotal-cell').innerHTML = '<small>Rp ' + formatRp(sub) + '</small>';
        total += sub;
    }
    document.getElementById('totalTerima').textContent = 'Rp ' + formatRp(total);
}

// Selisih otomatis saat qty diterima diubah
document.getElementById('formTerima').addEventListener('input', function (e) {
    var t = e.target;
    if (t.classList.contains('input-terima')) {
        var pesan = parseInt(t.getAttribute('data-pesan'), 10);
        var sudah = parseInt(t.getAttribute('data-diterima'), 10);
        var val = parseInt(t.value, 10) || 0;
        var max = Math.max(pesan - sudah, 0);

        if (val > max) { t.value = max; val = max; }

        var selisih = pesan - (sudah + val);
        var cell = t.closest('tr').querySelector('.selisih-cell');
        if (selisih <= 0) {
            cell.innerHTML = '<span class="badge bg-success">0</span>';
        } else {
            cell.innerHTML = '<span class="badge bg-warning text-dark">' + selisih + '</span>';
        }
    }

    if (t.classList.contains('input-terima') || t.classList.contains('input-harga')) {
        hitungTotalTerima();
    }
});

function validasiTerima() {
    var inputs = document.querySelectorAll('.input-terima');
    var total = 0;
    for (var i = 0; i < inputs.length; i++) {
        total += parseInt(inputs[i].value, 10) || 0;
    }
    if (total <= 0) {
        alert('Isi qty diterima minimal 1 untuk barang yang diterima.');
        return false;
    }
    return confirm('Simpan penerimaan barang? Stok akan bertambah otomatis.');
}
</script>
@endpush
@endsection
