@extends('layouts.app')

@section('title', 'Bayar Cicilan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Bayar Cicilan Piutang</h5>
    <a href="{{ route('piutang.show', $piutang->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Pelanggan</td>
                        <td class="fw-bold">{{ $piutang->nama_pelanggan }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Invoice</td>
                        <td><code>{{ $piutang->transaksi->no_invoice ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Piutang</td>
                        <td>Rp {{ number_format($piutang->total_piutang, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Sisa Piutang</td>
                        <td class="fw-bold text-danger fs-5">
                            Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cicilan ke</td>
                        <td>{{ $piutang->jml_cicilan_terbayar + 1 }} / {{ $piutang->max_cicilan }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Besar Cicilan</td>
                        <td>Rp {{ number_format($piutang->besar_cicilan, 0, ',', '.') }} /kali</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tenor</td>
                        <td>{{ $piutang->tenor_bulan }} bulan</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('piutang.bayar-cicilan', $piutang->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Pembayaran</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="jumlah" class="form-control form-control-lg"
                                value="{{ old('jumlah', min($piutang->besar_cicilan, $piutang->sisa_piutang)) }}"
                                min="1" max="{{ $piutang->sisa_piutang }}" required>
                        </div>
                        <small class="text-muted">
                            Min: Rp 1 | Max: Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}
                            @if($piutang->besar_cicilan > 0)
                            | Anjuran: Rp {{ number_format($piutang->besar_cicilan, 0, ',', '.') }}
                            @endif
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Metode Pembayaran</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metode_bayar"
                                    value="tunai" id="tunai" checked>
                                <label class="form-check-label" for="tunai">
                                    <i class="bi bi-cash me-1"></i> Tunai
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metode_bayar"
                                    value="transfer" id="transfer">
                                <label class="form-check-label" for="transfer">
                                    <i class="bi bi-credit-card me-1"></i> Transfer
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan (opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-1"></i> Konfirmasi Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
