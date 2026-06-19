<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_number'   => 'APT-' . fake()->unique()->numerify('######'),
            'patient_id'           => Patient::factory(),
            'doctor_id'            => Doctor::factory(),
            'appointment_datetime' => now()->addDays(fake()->numberBetween(1, 30)),
            'duration_minutes'     => fake()->randomElement([15, 20, 30, 45, 60]),
            'type'                 => fake()->randomElement(['opd', 'follow_up', 'emergency', 'teleconsultation']),
            'status'               => 'scheduled',
            'payment_status'       => 'pending',
            'fee'                  => fake()->numberBetween(500, 3000),
            'created_by'           => User::factory(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(['status' => 'confirmed']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }
}
