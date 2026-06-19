<?php

namespace Database\Factories;

use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ward> */
class WardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => fake()->unique()->word() . ' Ward',
            'code'       => fake()->unique()->bothify('W-##'),
            'ward_type'  => fake()->randomElement(['general', 'icu', 'emergency', 'maternity', 'paediatric']),
            'total_beds' => fake()->numberBetween(5, 30),
            'floor'      => fake()->numberBetween(1, 5),
            'status'     => 'active',
        ];
    }
}
