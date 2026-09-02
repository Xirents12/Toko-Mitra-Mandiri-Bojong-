<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMasukDetail extends Model
{
    protected $fillable = [
        'stok_masuk_id',
        'barang_id',
        'jumlah',
        'jumlah_diretur',
        'harga_beli',
        'keterangan',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function stokMasuk()
    {
        return $this->belongsTo(StokMasuk::class, 'stok_masuk_id');
    }

    /**
     * Sisa jumlah yang masih boleh diretur ke supplier (jumlah diterima - jumlah sudah diretur).
     */
    public function getSisaReturAttribute(): int
    {
        return max(0, (int) $this->jumlah - (int) ($this->jumlah_diretur ?? 0));
    }

    protected $casts = [
        'jumlah_diretur' => 'integer',
    ];
}
