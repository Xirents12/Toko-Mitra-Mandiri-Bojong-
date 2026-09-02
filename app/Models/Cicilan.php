<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cicilan extends Model
{
    protected $fillable = [
        'piutang_id', 'jumlah', 'tanggal_bayar', 'metode_bayar', 'keterangan', 'user_id'
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
    ];

    public function piutang()
    {
        return $this->belongsTo(Piutang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
