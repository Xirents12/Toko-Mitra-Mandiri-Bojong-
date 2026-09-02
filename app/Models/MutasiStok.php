<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiStok extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'tipe',        // 'masuk' atau 'keluar'
        'jumlah',
        'keterangan',
        'user_id',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper mencatat mutasi stok. tipe: 'masuk' atau 'keluar'.
     * Digunakan otomatis oleh modul stok masuk/keluar, POS, retur, dan penerimaan PO.
     */
    public static function catat(int $barangId, string $tipe, int $jumlah, string $keterangan = ''): void
    {
        self::create([
            'barang_id'  => $barangId,
            'tipe'       => $tipe,
            'jumlah'     => $jumlah,
            'keterangan' => $keterangan,
            'user_id'    => auth()->id(),
        ]);
    }
}