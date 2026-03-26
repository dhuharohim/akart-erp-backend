<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Event;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
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
            'invoice_number' => strtoupper(fake()->unique()->bothify('INV-####')),
            'issue_date' => now()->subDays(3),
            'due_date' => now()->addDays(14),
            'status' => fake()->randomElement(['issued', 'paid', 'partial']),
            'total_amount' => fake()->numberBetween(10000000, 45000000),
        ];
    }
}
