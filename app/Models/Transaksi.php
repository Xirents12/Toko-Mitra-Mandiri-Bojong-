<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksis';

    protected $fillable = [
        'user_id',
        'nama_kasir',
        'tanggal',
        'no_invoice',
        'total_harga',
        'metode_bayar',
        'status_kredit',
        'nama_pelanggan',
        'bayar',
        'kembalian',
    ];

    // Nama kasir: manual jika diisi, fallback ke nama user yang login
    public function getKasirNamaAttribute(): string
    {
        return $this->nama_kasir ?: ($this->user->name ?? '-');
    }

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function piutang()
    {
        return $this->hasOne(Piutang::class);
    }

    // Generate no_invoice: YYYYMMDDHHmmss + nomor urut harian (3 digit)
    public static function generateNoInvoice(): string
    {
        $now = now();
        $prefix = $now->format('YmdHis') . '-';

        // Nomor urut harian (reset setiap hari)
        $hariIni = $now->format('Y-m-d');
        $last = self::whereDate('created_at', $hariIni)
            ->where('no_invoice', 'like', $prefix . '%')
            ->orderBy('no_invoice', 'desc')
            ->first();

        $number = $last ? (int) substr($last->no_invoice, -3) + 1 : 1;

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    // Total penjualan hari ini
    public static function totalPenjualanHariIni()
    {
        return self::whereDate('created_at', today())
            ->where('metode_bayar', 'tunai')
            ->sum('total_harga');
    }

    // Total piutang hari ini
    public static function totalPiutangHariIni()
    {
        return self::whereDate('created_at', today())
            ->where('metode_bayar', 'kredit')
            ->sum('total_harga');
    }
}
