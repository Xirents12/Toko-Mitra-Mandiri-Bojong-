<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris'; // sesuaikan nama tabel

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    // Relasi balik ke Barang
    public function barangs()
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }
}
