<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Event;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'event_id' => Event::factory(),
            'vendor_id' => Vendor::factory(),
            'po_number' => strtoupper(fake()->unique()->bothify('PO-####')),
            'status' => fake()->randomElement(['draft', 'pending approval', 'approved']),
            'total_amount' => fake()->numberBetween(5000000, 25000000),
            'approved_at' => now(),
        ];
    }
}
