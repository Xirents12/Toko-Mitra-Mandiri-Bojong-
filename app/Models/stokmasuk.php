<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMasuk extends Model
{
    protected $fillable = [
        'no_transaksi',
        'tanggal_masuk',
        'supplier_id',
        'no_nota_supplier',
        'keterangan',
        'user_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(StokMasukDetail::class, 'stok_masuk_id');
    }

    /**
     * Nomor transaksi stok masuk otomatis, format: SM-YYYYMMDD-NNN (urutan per hari).
     * Contoh: SM-20260809-001 = stok masuk tanggal 09/08/2026, urutan ke-1.
     */
    public static function generateNoTransaksi(): string
    {
        $prefix = 'SM-' . date('Ymd') . '-';
        $last = self::where('no_transaksi', 'like', $prefix . '%')->latest()->first();
        $number = $last ? (int) substr($last->no_transaksi, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}