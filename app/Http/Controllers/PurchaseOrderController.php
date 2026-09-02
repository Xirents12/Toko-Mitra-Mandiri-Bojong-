<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\TerimaPurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\PoDetail;
use App\Models\PurchaseOrder;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchaseOrderController extends Controller
{
    /**
     * Daftar Purchase Order (dengan filter status & supplier).
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'details.barang']);

        if ($request->filled('search')) {
            $query->where('no_po', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // DataTables client-side: ambil semua data, pencarian/sorting/pagination di-handle DataTables
        $pos = $query->orderByDesc('created_at')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        $barangKritis = Barang::where('is_active', true)
            ->whereColumn('stok_saat_ini', '<=', 'stok_minimum')
            ->orderBy('nama_barang')
            ->get();

        return view('purchase_order.index', compact('pos', 'suppliers', 'barangKritis'));
    }

    /**
     * Landing "Penerimaan Barang" khusus Gudang: PO yang siap diterima.
     */
    public function penerimaan()
    {
        $pos = PurchaseOrder::with(['supplier', 'user', 'details.barang'])
            ->whereIn('status', [
                PurchaseOrder::STATUS_DISETUJUI,
                PurchaseOrder::STATUS_DIKIRIM,
                PurchaseOrder::STATUS_DITERIMA_SEBAGIAN,
            ])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('purchase_order.penerimaan', compact('pos'));
    }

    /**
     * Form tambah PO — pemesanan barang kritis.
     * Bagian "Barang Kritis" di halaman form: klik barang → masuk pesanan (qty = stok minimum).
     * Dukung prefill dari notifikasi stok kritis (?barang_ids=1,2,3).
     */
    public function create(Request $request)
    {
        $barangs = Barang::where('is_active', true)
            ->with(['kategori', 'preferredSupplier'])
            ->orderBy('nama_barang')
            ->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        $noPo = PurchaseOrder::generateNoPo();

        // Barang kritis/minimum untuk quick-add di form pesanan
        $barangKritis = Barang::with('preferredSupplier')
            ->where('is_active', true)
            ->whereColumn('stok_saat_ini', '<=', 'stok_minimum')
            ->orderBy('stok_saat_ini')
            ->get();
        $kritisIds = $barangKritis->pluck('id')->all();

        $prefill = [];
        if ($request->filled('barang_ids')) {
            $prefill = collect(explode(',', $request->barang_ids))
                ->map(fn ($v) => (int) trim($v))
                ->filter()
                ->values()
                ->all();
        }

        return view('purchase_order.create', compact('barangs', 'suppliers', 'noPo', 'prefill', 'barangKritis', 'kritisIds'));
    }

    /**
     * Simpan PO baru (Draft) atau langsung ajukan ke Pemilik.
     *
     * Setiap item barang membawa supplier-nya sendiri (items.*.supplier_id);
     * item dikelompokkan per supplier -> otomatis dibuat 1 permintaan per supplier,
     * sehingga barang dari 2 supplier berbeda menghasilkan 2 PO terpisah.
     * Supplier header dipakai sebagai fallback untuk item yang tidak punya supplier.
     */
    public function store(StorePurchaseOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $isAjukan = $request->action === 'ajukan';

            // Gudang membuat PERMINTAAN; admin yang menyetujui permintaan.
            $status = $isAjukan ? PurchaseOrder::STATUS_PERMINTAAN : PurchaseOrder::STATUS_DRAFT;

            // Kelompokkan item per supplier (1 permintaan per supplier)
            $groups = [];
            foreach ($request->items as $item) {
                $supId = !empty($item['supplier_id'])
                    ? (int) $item['supplier_id']
                    : (int) $request->supplier_id;

                if ($supId <= 0) {
                    throw new \Exception('Pilih supplier untuk setiap item barang.');
                }

                $groups[$supId][] = $item;
            }

            $noPos = [];

            foreach ($groups as $supId => $items) {
                $supplier = Supplier::find($supId);
                if (!$supplier) {
                    throw new \Exception('Supplier tidak valid.');
                }

                $po = PurchaseOrder::create([
                    'no_po'            => PurchaseOrder::generateNoPo(),
                    'tanggal_po'       => $request->tanggal_po,
                    'estimasi_datang'  => $request->estimasi_datang ?: now()->addDays(7)->toDateString(),
                    'supplier_id'      => $supId,
                    'status'           => $status,
                    'catatan'          => $request->catatan,
                    'user_id'          => $user->id,
                ]);

                $this->simpanDetailItems($po, $items);
                $noPos[] = $po->no_po . ' (' . $supplier->nama_supplier . ')';
            }

            DB::commit();

            if (count($noPos) === 1) {
                $msg = $isAjukan
                    ? 'Permintaan barang berhasil dikirim ke admin/owner untuk disetujui.'
                    : 'Permintaan barang berhasil disimpan sebagai Draft.';
            } else {
                $msg = 'Berhasil membuat ' . count($noPos) . ' permintaan barang terpisah per supplier: '
                    . implode(', ', $noPos)
                    . ($isAjukan
                        ? ' — dikirim ke admin/owner untuk disetujui.'
                        : ' — disimpan sebagai Draft.');
            }

            return redirect()->route('purchase-order.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat PO: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Buat PERMINTAAN otomatis dari barang stok kritis (sekali klik oleh Gudang).
     *
     * - Qty otomatis = 1x stok minimum (minimal 1).
     * - Barang dikelompokkan per supplier rekomendasi -> 1 permintaan per supplier.
     * - Barang yang sudah punya permintaan/PO berjalan dilewati (anti dobel).
     * - Status "Permintaan" -> menunggu persetujuan admin/owner.
     *
     * Opsional param ?barang_ids=1,2,3 untuk membatasi ke barang tertentu.
     */
    public function buatOtomatis(Request $request)
    {
        Gate::authorize('create', PurchaseOrder::class);

        $query = Barang::with('preferredSupplier')
            ->where('is_active', true)
            ->whereColumn('stok_saat_ini', '<=', 'stok_minimum');

        // Batasi ke barang tertentu jika dikirim dari tombol per-item
        if ($request->filled('barang_ids')) {
            $ids = collect(explode(',', $request->barang_ids))
                ->map(fn ($v) => (int) trim($v))
                ->filter()
                ->values()
                ->all();

            if ($ids) {
                $query->whereIn('id', $ids);
            }
        }

        $barangs = $query->orderBy('nama_barang')->get();

        if ($barangs->isEmpty()) {
            return back()->with('info', 'Tidak ada barang stok kritis yang perlu dibuatkan PO.');
        }

        // Status PO yang masih "berjalan" (belum selesai) -> cegah pesan ganda.
        // STATUS_PERMINTAAN ikut dicegah: gudang hanya boleh membuat 1 permintaan
        // per barang sampai admin membuat PO-nya.
        $statusBerjalan = [
            PurchaseOrder::STATUS_PERMINTAAN,
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_MENUNGGU,
            PurchaseOrder::STATUS_DISETUJUI,
            PurchaseOrder::STATUS_DIKIRIM,
            PurchaseOrder::STATUS_DITERIMA_SEBAGIAN,
        ];
        $idDalamPoAktif = PoDetail::whereHas('purchaseOrder', function ($q) use ($statusBerjalan) {
            $q->whereIn('status', $statusBerjalan);
        })->pluck('barang_id')->unique()->flip()->all();

        // Kelompokkan barang per supplier rekomendasi
        $groups = [];
        $dilewati = [];

        foreach ($barangs as $b) {
            if (isset($idDalamPoAktif[$b->id])) {
                $dilewati[] = $b->nama_barang . ' (sudah ada permintaan/PO berjalan)';
                continue;
            }

            $supplier = $b->supplier_rekomendasi;
            if (!$supplier) {
                $dilewati[] = $b->nama_barang . ' (belum ada supplier)';
                continue;
            }

            $groups[$supplier->id][] = [
                'supplier' => $supplier,
                'barang'   => $b,
                'qty'      => max((int) $b->stok_minimum, 1),
                'harga'    => (float) ((float) $b->harga_beli_terakhir ?: $b->harga_beli),
            ];
        }

        if (empty($groups)) {
            return back()->with('info', 'Tidak ada barang kritis yang bisa dibuatkan PO otomatis. ' . implode('; ', $dilewati));
        }

        DB::beginTransaction();

        try {
            $noPos = [];

            foreach ($groups as $items) {
                $supplier = $items[0]['supplier'];

                $po = PurchaseOrder::create([
                    'no_po'           => PurchaseOrder::generateNoPo(),
                    'tanggal_po'      => now()->toDateString(),
                    'estimasi_datang' => now()->addDays(7)->toDateString(),
                    'supplier_id'     => $supplier->id,
                    'status'          => PurchaseOrder::STATUS_PERMINTAAN,
                    'catatan'         => 'Permintaan otomatis dari stok kritis, dibuat ' . now()->format('d/m/Y H:i'),
                    'user_id'         => auth()->id(),
                ]);

                foreach ($items as $item) {
                    PoDetail::create([
                        'po_id'      => $po->id,
                        'barang_id'  => $item['barang']->id,
                        'jumlah'     => $item['qty'],
                        'harga_beli' => $item['harga'],
                        'subtotal'   => $item['qty'] * $item['harga'],
                    ]);
                }

                $noPos[] = $po->no_po . ' (' . $supplier->nama_supplier . ')';
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat PO otomatis: ' . $e->getMessage());
        }

        $msg = 'Berhasil membuat ' . count($noPos) . ' permintaan barang dari stok kritis ('
            . implode(', ', $noPos) . '). Menunggu persetujuan admin/owner.';

        if (!empty($dilewati)) {
            $msg .= ' Dilewati: ' . implode('; ', array_slice($dilewati, 0, 5));
            if (count($dilewati) > 5) {
                $msg .= ' (+' . (count($dilewati) - 5) . ' lainnya)';
            }
        }

        return back()->with('success', $msg);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'details.barang']);
        return view('purchase_order.show', compact('purchaseOrder'));
    }

    /**
     * Ubah PO yang masih Draft.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', 'PO yang sudah diproses tidak dapat diubah.');
        }

        $purchaseOrder->load('details.barang.preferredSupplier');
        $barangs = Barang::where('is_active', true)->with(['kategori', 'preferredSupplier'])->orderBy('nama_barang')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();

        return view('purchase_order.edit', compact('purchaseOrder', 'barangs', 'suppliers'));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        DB::beginTransaction();

        try {
            $purchaseOrder->update([
                'tanggal_po'      => $request->tanggal_po,
                'estimasi_datang' => $request->estimasi_datang,
                'supplier_id'     => $request->supplier_id,
                'catatan'         => $request->catatan,
            ]);

            $purchaseOrder->details()->delete();
            $this->simpanDetailItems($purchaseOrder, $request->items);

            DB::commit();

            return redirect()->route('purchase-order.show', $purchaseOrder->id)
                ->with('success', 'Purchase Order berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui PO: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Gudang mengajukan Draft -> Permintaan (menunggu admin membuat PO).
     */
    public function ajukan(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('ajukan', $purchaseOrder);

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_PERMINTAAN]);

        return redirect()->route('purchase-order.index')
            ->with('success', 'Permintaan dikirim ke admin/owner untuk disetujui.');
    }

    /**
     * DIMATIKAN: "Buat PO ke Supplier" untuk admin — admin cukup Setujui/Tolak permintaan.
     * (Route & tombol sudah dikomentari; method ini hanya dipertahankan untuk referensi.)
     */
    // public function buatPo(PurchaseOrder $purchaseOrder)
    // {
    //     Gate::authorize('buatPo', $purchaseOrder);
    //
    //     $purchaseOrder->update(['status' => PurchaseOrder::STATUS_DISETUJUI]);
    //
    //     return redirect()->route('purchase-order.index')
    //         ->with('success', 'PO berhasil dibuat ke supplier (' . ($purchaseOrder->supplier->nama_supplier ?? '-') . '). Status: Disetujui.');
    // }

    /**
     * Pemilik menyetujui PO lama (Menunggu -> Disetujui).
     */
    public function setujui(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('setujui', $purchaseOrder);

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_DISETUJUI]);

        return redirect()->route('purchase-order.index')
            ->with('success', 'PO disetujui. Gudang dapat melakukan penerimaan barang.');
    }

    /**
     * Pemilik menolak permintaan / PO (Permintaan / Menunggu -> Ditolak).
     */
    public function tolak(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('tolak', $purchaseOrder);

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_DITOLAK]);

        return redirect()->route('purchase-order.index')
            ->with('success', 'Permintaan/PO ditolak.');
    }

    /**
     * Menandai PO telah dikirim supplier (Disetujui -> Dikirim Supplier).
     */
    public function kirim(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('kirim', $purchaseOrder);

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_DIKIRIM]);

        return redirect()->route('purchase-order.index')
            ->with('success', 'PO ditandai sebagai "Dikirim Supplier".');
    }

    /**
     * Membatalkan PO (Draft / Menunggu -> Dibatalkan).
     */
    public function batalkan(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('batalkan', $purchaseOrder);

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_DIBATALKAN]);

        return redirect()->route('purchase-order.index')
            ->with('success', 'PO dibatalkan.');
    }

    /**
     * Hapus PO Draft (soft delete).
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('delete', $purchaseOrder);

        $purchaseOrder->details()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchase-order.index')
            ->with('success', 'PO berhasil dihapus.');
    }

    /**
     * Form penerimaan barang (Gudang). Menampilkan qty pesan, qty sudah diterima, selisih.
     */
    public function terima(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->bisa_diterima) {
            return back()->with('error', 'PO hanya dapat diterima jika berstatus Disetujui, Dikirim Supplier, atau Diterima Sebagian.');
        }

        $purchaseOrder->load(['supplier', 'user', 'details.barang']);

        return view('purchase_order.terima', compact('purchaseOrder'));
    }

    /**
     * Proses penerimaan barang: update qty_diterima, stok bertambah, catat ke stok masuk.
     */
    public function terimaSimpan(TerimaPurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->bisa_diterima) {
            return back()->with('error', 'PO tidak dapat diproses penerimaan pada status ini.');
        }

        DB::beginTransaction();

        try {
            $stokMasuk = StokMasuk::create([
                'no_transaksi'      => StokMasuk::generateNoTransaksi(),
                'tanggal_masuk'     => now()->toDateString(),
                'supplier_id'       => $purchaseOrder->supplier_id,
                'no_nota_supplier'  => $purchaseOrder->no_po,
                'keterangan'        => 'Penerimaan dari PO: ' . $purchaseOrder->no_po . ($purchaseOrder->catatan ? ' - ' . $purchaseOrder->catatan : ''),
                'user_id'           => Auth::id(),
            ]);

            $totalDiterima = 0;

            foreach ($request->items as $item) {
                $detail = PoDetail::where('id', $item['detail_id'])
                    ->where('po_id', $purchaseOrder->id)
                    ->first();

                if (!$detail) {
                    throw new \Exception('Data barang tidak valid.');
                }

                // Sisa yang belum diterima (tidak boleh melebihi qty pesan)
                $sisa = $detail->jumlah - $detail->qty_diterima;
                $qtyTerima = min((int) $item['qty_diterima'], max($sisa, 0));

                if ($qtyTerima <= 0) {
                    continue;
                }

                // Harga ASLI dikonfirmasi saat barang datang (bukan estimasi dari PO)
                $hargaAktual = (float) ($item['harga_beli'] ?? $detail->harga_beli);

                $detail->increment('qty_diterima', $qtyTerima);

                // Harga aktual menggantikan estimasi pada detail PO (subtotal = qty pesan x harga asli)
                $detail->update([
                    'harga_beli' => $hargaAktual,
                    'subtotal'   => $detail->jumlah * $hargaAktual,
                ]);

                // Stok otomatis bertambah sesuai qty yang benar-benar diterima
                Barang::where('id', $detail->barang_id)
                    ->increment('stok_saat_ini', $qtyTerima);

                StokMasukDetail::create([
                    'stok_masuk_id' => $stokMasuk->id,
                    'barang_id'     => $detail->barang_id,
                    'jumlah'        => $qtyTerima,
                    'harga_beli'    => $hargaAktual,
                    'keterangan'    => 'Dari PO: ' . $purchaseOrder->no_po,
                ]);

                // Harga beli terakhir barang mengikuti harga asli saat barang diterima
                Barang::where('id', $detail->barang_id)
                    ->update(['harga_beli_terakhir' => $hargaAktual]);

                // Catat mutasi stok masuk (penerimaan PO)
                MutasiStok::catat($detail->barang_id, 'masuk', $qtyTerima, 'Penerimaan ' . $purchaseOrder->no_po);

                $totalDiterima += $qtyTerima;
            }

            if ($totalDiterima <= 0) {
                DB::rollBack();
                return back()->with('error', 'Tidak ada barang yang diterima. Isi qty diterima minimal 1.');
            }

            // Status: semua diterima penuh -> Selesai, sebagian -> Diterima Sebagian
            $status = $purchaseOrder->sudah_diterima_lengkap
                ? PurchaseOrder::STATUS_SELESAI
                : PurchaseOrder::STATUS_DITERIMA_SEBAGIAN;

            $purchaseOrder->update(['status' => $status]);

            DB::commit();

            return redirect()->route('purchase-order.index')
                ->with('success', $status === PurchaseOrder::STATUS_SELESAI
                    ? 'Barang diterima lengkap. PO selesai dan stok sudah bertambah.'
                    : 'Barang diterima sebagian. Status PO: Diterima Sebagian. Stok sudah bertambah.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Riwayat pembelian (semua status, termasuk ditolak/dibatalkan).
     */
    public function riwayat(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'details.barang']);

        if ($request->filled('search')) {
            $query->where('no_po', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_po', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_po', '<=', $request->sampai);
        }

        $riwayats = $query->orderByDesc('tanggal_po')->paginate(20)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();

        return view('purchase_order.riwayat', compact('riwayats', 'suppliers'));
    }

    /**
     * Laporan Purchase Order (filter periode, supplier, status) + ringkasan.
     */
    public function laporan(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'details.barang']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_po', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_po', '<=', $request->sampai);
        }

        $reports = $query->orderByDesc('tanggal_po')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();

        $totalPO = $reports->count();
        $totalNilai = $reports->sum('total');
        $totalDiterima = $reports->sum('total_diterima');

        return view('purchase_order.laporan', compact('reports', 'suppliers', 'totalPO', 'totalNilai', 'totalDiterima'));
    }

    /**
     * Cetak / print PO (versi printable).
     */
    public function cetak(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'details.barang']);
        return view('purchase_order.cetak', compact('purchaseOrder'));
    }

    /**
     * Simpan item PO ke po_details + perbarui harga beli terakhir.
     * Barang yang sama otomatis digabung (anti baris dobel / stok dobel).
     *
     * Catatan: nama kolom mengikuti tabel lama `po_details` (jumlah = qty pesan),
     * bukan `purchase_order_details`/`qty_pesan` pada spesifikasi, agar kompatibel
     * dengan data & relasi yang sudah ada.
     */
    private function simpanDetailItems(PurchaseOrder $po, array $items): void
    {
        $merged = [];
        foreach ($items as $item) {
            $id = (int) $item['barang_id'];
            if (!isset($merged[$id])) {
                $merged[$id] = [
                    'barang_id'  => $id,
                    'jumlah'     => (int) $item['jumlah'],
                    'harga_beli' => (float) $item['harga_beli'],
                ];
            } else {
                $merged[$id]['jumlah'] += (int) $item['jumlah'];
            }
        }

        foreach ($merged as $m) {
            PoDetail::create([
                'po_id'      => $po->id,
                'barang_id'  => $m['barang_id'],
                'jumlah'     => $m['jumlah'],
                'harga_beli' => $m['harga_beli'],
                'subtotal'   => $m['jumlah'] * $m['harga_beli'],
            ]);

            // Catatan: harga di PO adalah ESTIMASI. harga_beli_terakhir barang
            // baru diperbarui saat barang benar-benar diterima (lihat terimaSimpan).
        }
    }
}
