<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Doctor> */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory()->state(['user_type' => 'doctor']),
            'doctor_id'      => 'DR-' . fake()->unique()->numerify('######'),
            'specialization' => fake()->randomElement(['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'General Medicine']),
            'qualification'  => fake()->randomElement(['MBBS', 'FCPS', 'MBBS']),
            'cnic'           => fake()->numerify('#####-#######-#'),
            'phone'          => fake()->numerify('03#########'),
            'status'         => 'active',
        ];
    }
}
