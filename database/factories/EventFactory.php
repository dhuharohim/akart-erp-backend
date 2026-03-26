<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $revenue = fake()->numberBetween(50000000, 200000000);
        $expense = fake()->numberBetween(20000000, 120000000);

        return [
            'company_id' => Company::factory(),
            'event_number' => strtoupper(fake()->unique()->bothify('EVT-####')),
            'name' => fake()->sentence(3),
            'venue' => fake()->city(),
            'status' => fake()->randomElement(['planning', 'confirmed', 'running', 'completed']),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'timeline' => [['phase' => 'planning', 'date' => now()->toDateString()]],
            'budget_amount' => $revenue,
            'revenue_amount' => $revenue,
            'expense_amount' => $expense,
            'profit_amount' => $revenue - $expense,
        ];
    }
}
