<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'no_po'            => PurchaseOrder::generateNoPo(),
            'tanggal_po'       => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'estimasi_datang'  => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+14 days')->format('Y-m-d') : null,
            'supplier_id'      => Supplier::inRandomOrder()->value('id') ?? Supplier::factory(),
            'user_id'          => User::inRandomOrder()->value('id') ?? User::factory(),
            'status'           => fake()->randomElement([
                PurchaseOrder::STATUS_DRAFT,
                PurchaseOrder::STATUS_MENUNGGU,
                PurchaseOrder::STATUS_DISETUJUI,
                PurchaseOrder::STATUS_DIKIRIM,
                PurchaseOrder::STATUS_DITERIMA_SEBAGIAN,
                PurchaseOrder::STATUS_SELESAI,
            ]),
            'catatan'          => fake()->optional(0.6)->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => PurchaseOrder::STATUS_DRAFT]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => ['status' => PurchaseOrder::STATUS_SELESAI]);
    }
}
