<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
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
            'department_id' => Department::query()->value('id'),
            'employee_type' => fake()->randomElement(['internal', 'freelancer']),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'position' => fake()->jobTitle(),
            'salary' => fake()->numberBetween(15000000, 40000000),
            'hourly_rate' => fake()->numberBetween(150000, 350000),
            'project_rate' => fake()->numberBetween(2000000, 8000000),
        ];
    }
}
