<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokKeluarDetail extends Model
{
    protected $table = 'stok_keluar_details';

    protected $fillable = [
        'stok_keluar_id',
        'barang_id',
        'jumlah',
        'harga_jual',
        'harga_beli',
        'keterangan',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function stokKeluar()
    {
        return $this->belongsTo(StokKeluar::class, 'stok_keluar_id');
    }

    // Untuk retur ke supplier nilai dihitung dari harga_beli; fallback ke harga_jual.
    public function getSubtotalAttribute(): float
    {
        return $this->jumlah * ($this->harga_beli ?: $this->harga_jual);
    }
}
