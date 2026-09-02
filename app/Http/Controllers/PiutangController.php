<?php

namespace App\Http\Controllers;

use App\Models\Piutang;
use App\Models\Cicilan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PiutangController extends Controller
{
    public function index(Request $request)
    {
        $query = Piutang::with(['transaksi', 'user']);

        if ($request->search) {
            $query->where('nama_pelanggan', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $piutangs = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $totalPiutang = Piutang::where('status', 'aktif')->sum('sisa_piutang');
        $totalLunas = Piutang::where('status', 'lunas')->sum('total_piutang');

        return view('piutang.index', compact('piutangs', 'totalPiutang', 'totalLunas'));
    }

    public function show(Piutang $piutang)
    {
        $piutang->load(['transaksi.detailTransaksi.barang', 'user', 'cicilans.user']);
        return view('piutang.show', compact('piutang'));
    }

    public function formBayar(Piutang $piutang)
    {
        $piutang->load('cicilans');
        return view('piutang.bayar', compact('piutang'));
    }

    public function bayarCicilan(Request $request, Piutang $piutang)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:tunai,transfer',
            'keterangan' => 'nullable|string',
        ]);

        if ($piutang->status === 'lunas') {
            return back()->with('error', 'Piutang ini sudah lunas.');
        }

        $sisaSetelah = $piutang->sisa_piutang - $request->jumlah;

        if ($sisaSetelah < 0) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa piutang. Sisa: Rp ' . number_format($piutang->sisa_piutang, 0, ',', '.'));
        }

        DB::beginTransaction();

        try {
            Cicilan::create([
                'piutang_id' => $piutang->id,
                'jumlah' => $request->jumlah,
                'tanggal_bayar' => now()->toDateString(),
                'metode_bayar' => $request->metode_bayar,
                'keterangan' => $request->keterangan,
                'user_id' => Auth::id(),
            ]);

            $piutang->sisa_piutang = $sisaSetelah;
            $piutang->jml_cicilan_terbayar += 1;

            if ($sisaSetelah <= 0) {
                $piutang->status = 'lunas';
                // Update status transaksi
                Transaksi::where('id', $piutang->transaksi_id)
                    ->update(['status_kredit' => 'lunas']);
            }

            $piutang->save();

            DB::commit();

            return redirect()->route('piutang.show', $piutang->id)
                ->with('success', 'Pembayaran cicilan berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    public function laporan(Request $request)
    {
        $query = Piutang::with('transaksi');

        if ($request->tanggal_awal && $request->tanggal_akhir) {
            $query->whereBetween('created_at', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        $piutangs = $query->orderByDesc('created_at')->get();

        $totalPiutang = $piutangs->sum('total_piutang');
        $totalSisa = $piutangs->sum('sisa_piutang');
        $totalTerkumpul = $piutangs->sum(function ($p) {
            return $p->total_piutang - $p->sisa_piutang;
        });

        return view('piutang.laporan', compact('piutangs', 'totalPiutang', 'totalSisa', 'totalTerkumpul'));
    }
}
