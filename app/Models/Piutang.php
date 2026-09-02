<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    protected $fillable = [
        'transaksi_id', 'user_id', 'nama_pelanggan', 'no_telepon', 'alamat',
        'total_piutang', 'sisa_piutang', 'max_cicilan', 'jml_cicilan_terbayar',
        'besar_cicilan', 'tenor_bulan', 'tanggal_jatuh_tempo', 'status', 'keterangan'
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cicilans()
    {
        return $this->hasMany(Cicilan::class);
    }

    public function getSisaCicilanAttribute(): int
    {
        return $this->max_cicilan - $this->jml_cicilan_terbayar;
    }
}
