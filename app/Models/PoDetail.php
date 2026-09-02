<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoDetail extends Model
{
    use HasFactory;

    protected $table = 'po_details';

    protected $fillable = [
        'po_id', 'barang_id', 'jumlah', 'qty_diterima', 'harga_beli', 'subtotal'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
