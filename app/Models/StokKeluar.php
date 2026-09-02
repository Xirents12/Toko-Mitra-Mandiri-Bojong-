<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokKeluar extends Model
{
    protected $fillable = ['no_transaksi','tanggal_keluar','nama_pelanggan','jenis_keluar','keterangan','supplier_id','user_id'];

    public function user()     { return $this->belongsTo(User::class); }
    public function supplier() { return $this->belongsTo(Supplier::class, 'supplier_id'); }
    public function details()  { return $this->hasMany(StokKeluarDetail::class); }

    public static function generateNoTransaksi(): string
    {
        $prefix = 'SK-' . date('Ym') . '-';
        $last = self::where('no_transaksi', 'like', $prefix . '%')->latest()->first();
        $number = $last ? (int) substr($last->no_transaksi, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Nomor transaksi retur ke supplier: RS-YYYYMMDD-NNN (reset urutan tiap hari).
     */
    public static function generateNoReturSupplier(): string
    {
        $prefix = 'RS-' . date('Ymd') . '-';
        $last = self::where('no_transaksi', 'like', $prefix . '%')->latest()->first();
        $number = $last ? (int) substr($last->no_transaksi, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function getTotalAttribute(): float
    {
        return $this->details->sum(fn($d) => $d->jumlah * ($d->harga_beli ?: $d->harga_jual));
    }
}
