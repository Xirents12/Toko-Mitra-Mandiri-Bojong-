<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'no_po', 'tanggal_po', 'estimasi_datang', 'supplier_id', 'status', 'catatan', 'user_id',
    ];

    protected $casts = [
        'tanggal_po' => 'date',
        'estimasi_datang' => 'date',
    ];

    // ───────── Status workflow PO ─────────
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PERMINTAAN = 'permintaan';
    public const STATUS_MENUNGGU = 'menunggu_persetujuan';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DIKIRIM = 'dikirim_supplier';
    public const STATUS_DITERIMA_SEBAGIAN = 'diterima_sebagian';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUS_LABEL = [
        'draft'                => 'Draft',
        'permintaan'           => 'Permintaan Barang',
        'menunggu_persetujuan' => 'Menunggu Persetujuan',
        'disetujui'            => 'Disetujui',
        'dikirim_supplier'     => 'Dikirim Supplier',
        'diterima_sebagian'    => 'Diterima Sebagian',
        'selesai'              => 'Selesai',
        'ditolak'              => 'Ditolak',
        'dibatalkan'           => 'Dibatalkan',
    ];

    public const STATUS_COLOR = [
        'draft'                => 'secondary',
        'permintaan'           => 'warning',
        'menunggu_persetujuan' => 'warning',
        'disetujui'            => 'info',
        'dikirim_supplier'     => 'primary',
        'diterima_sebagian'    => 'warning',
        'selesai'              => 'success',
        'ditolak'              => 'danger',
        'dibatalkan'           => 'dark',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PoDetail::class, 'po_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLOR[$this->status] ?? 'secondary';
    }

    public function getTotalAttribute()
    {
        return $this->details->sum('subtotal');
    }

    public function getTotalDipesanAttribute(): int
    {
        return $this->details->sum('jumlah');
    }

    public function getTotalDiterimaAttribute(): int
    {
        return $this->details->sum('qty_diterima');
    }

    /** Semua item sudah diterima penuh? */
    public function getSudahDiterimaLengkapAttribute(): bool
    {
        if ($this->details->isEmpty()) {
            return false;
        }
        return $this->details->every(fn ($d) => $d->qty_diterima >= $d->jumlah);
    }

    /** Status yang boleh diproses penerimaan barang oleh gudang */
    public function getBisaDiterimaAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_DISETUJUI,
            self::STATUS_DIKIRIM,
            self::STATUS_DITERIMA_SEBAGIAN,
        ]);
    }

    /**
     * Nomor PO otomatis, format: PO-YYYYMMDDHH-NNN (tanggal+jam + urutan, total 13 angka).
     * Contoh: PO-2026080910-001 = PO tanggal 09/08/2026 jam 10, urutan ke-1.
     * Urutan dimulai dari 001 untuk setiap jam; nomor dijamin unik.
     */
    public static function generateNoPo(): string
    {
        $prefix = 'PO-' . date('YmdH') . '-';

        // Cari nomor terakhir berformat PO-YYYYMMDDHH-NNN pada jam yang sama
        $existing = self::withTrashed()
            ->where('no_po', 'like', $prefix . '%')
            ->pluck('no_po');

        $next = 1;
        foreach ($existing as $no) {
            // Hanya hitung nomor dengan format persis PO-10digit-NNN (abaikan format lama)
            if (preg_match('/^PO-\d{10}-(\d+)$/', $no, $m)) {
                $next = max($next, (int) $m[1] + 1);
            }
        }

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
