<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\PoDetail;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoDetail>
 */
class PoDetailFactory extends Factory
{
    public function definition(): array
    {
        $barang = Barang::inRandomOrder()->first() ?? Barang::factory();
        $jumlah = fake()->numberBetween(1, 50);
        $harga  = $barang->harga_beli ?: fake()->numberBetween(10000, 1000000);

        return [
            'po_id'         => PurchaseOrder::inRandomOrder()->value('id') ?? PurchaseOrder::factory(),
            'barang_id'     => $barang->id ?? $barang,
            'jumlah'        => $jumlah,
            'qty_diterima'  => 0,
            'harga_beli'    => $harga,
            'subtotal'      => $jumlah * $harga,
        ];
    }
}
