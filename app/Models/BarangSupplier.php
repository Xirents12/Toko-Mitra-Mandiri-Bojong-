<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BarangSupplier extends Pivot
{
    protected $table = 'barang_supplier';

    protected $fillable = [
        'barang_id', 'supplier_id', 'harga_beli_terakhir', 'is_preferred'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
