<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->numberBetween(1000000, 15000000),
            'paid_at' => now(),
            'method' => fake()->randomElement(['bank_transfer', 'card', 'cash']),
            'reference_number' => strtoupper(fake()->bothify('PAY-#######')),
        ];
    }
}
