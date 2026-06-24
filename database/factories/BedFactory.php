<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Bed> */
class BedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ward_id' => Ward::factory(),
            'bed_number' => fake()->unique()->bothify('B-###'),
            'bed_type' => fake()->randomElement(['standard', 'electric', 'bariatric', 'pediatric']),
            'charge_per_day' => fake()->numberBetween(500, 5000),
            'status' => 'available',
        ];
    }

    public function occupied(): static
    {
        return $this->state(['status' => 'occupied']);
    }
}
