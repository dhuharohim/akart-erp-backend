<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Vendor;
use App\Models\VendorService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
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
            'name' => fake()->company(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'vendor_service_id' => VendorService::query()->value('id'),
            'rating' => fake()->numberBetween(3, 5),
        ];
    }
}
