<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Patient> */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mr_number'     => 'MR-' . fake()->unique()->numerify('######'),
            'name'          => fake()->name(),
            'phone'         => fake()->numerify('03#########'),
            'email'         => fake()->optional()->safeEmail(),
            'gender'        => fake()->randomElement(['male', 'female']),
            'age'           => fake()->numberBetween(1, 90),
            'age_unit'      => 'years',
            'blood_group'   => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'unknown']),
            'address'       => fake()->address(),
            'city'          => fake()->city(),
            'status'        => 'active',
        ];
    }
}
