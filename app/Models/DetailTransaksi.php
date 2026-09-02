<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'transaksi_details';

    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'jumlah',
        'jumlah_diretur',
        'harga_satuan',
        'harga_beli',
        'subtotal',
    ];

    /**
     * Sisa jumlah yang masih boleh diretur (jumlah dibeli - jumlah sudah diretur).
     */
    public function getSisaReturAttribute(): int
    {
        return max(0, (int) $this->jumlah - (int) ($this->jumlah_diretur ?? 0));
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    protected $casts = [
        'jumlah_diretur' => 'integer',
    ];
}