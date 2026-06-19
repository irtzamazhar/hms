<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Notification;

class AppointmentApiTest extends ApiTestCase
{
    public function test_can_list_appointments(): void
    {
        Appointment::factory()->count(2)->create();

        $this->getJson('/api/appointments', $this->asAdmin())
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_create_appointment(): void
    {
        Notification::fake();

        $patient = Patient::factory()->create();
        $doctor  = Doctor::factory()->create();

        $response = $this->postJson('/api/appointments', [
            'patient_id'           => $patient->id,
            'doctor_id'            => $doctor->id,
            'appointment_datetime' => now()->addDay()->toDateTimeString(),
            'type'                 => 'opd',
            'fee'                  => 1500,
        ], $this->asAdmin());

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'appointment_number', 'patient', 'doctor', 'status']]);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
            'status'     => 'scheduled',
        ]);
    }

    public function test_cannot_create_appointment_in_the_past(): void
    {
        $patient = Patient::factory()->create();
        $doctor  = Doctor::factory()->create();

        $this->postJson('/api/appointments', [
            'patient_id'           => $patient->id,
            'doctor_id'            => $doctor->id,
            'appointment_datetime' => now()->subDay()->toDateTimeString(),
            'type'                 => 'opd',
        ], $this->asAdmin())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('appointment_datetime');
    }

    public function test_create_appointment_requires_patient_and_doctor(): void
    {
        $this->postJson('/api/appointments', [
            'appointment_datetime' => now()->addDay()->toDateTimeString(),
            'type'                 => 'opd',
        ], $this->asAdmin())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['patient_id', 'doctor_id']);
    }

    public function test_can_show_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $this->getJson("/api/appointments/{$appointment->id}", $this->asAdmin())
            ->assertOk()
            ->assertJsonFragment(['id' => $appointment->id]);
    }

    public function test_can_update_appointment_status(): void
    {
        $appointment = Appointment::factory()->create();

        $this->patchJson("/api/appointments/{$appointment->id}/status", [
            'status' => 'confirmed',
        ], $this->asAdmin())
            ->assertOk()
            ->assertJsonFragment(['status' => 'confirmed']);

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'confirmed']);
    }

    public function test_update_status_validates_allowed_values(): void
    {
        $appointment = Appointment::factory()->create();

        $this->patchJson("/api/appointments/{$appointment->id}/status", [
            'status' => 'invalid_status',
        ], $this->asAdmin())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_can_filter_appointments_by_status(): void
    {
        Appointment::factory()->create(['status' => 'scheduled']);
        Appointment::factory()->confirmed()->create();

        $response = $this->getJson('/api/appointments?status=confirmed', $this->asAdmin())
            ->assertOk();

        foreach ($response->json('data') as $apt) {
            $this->assertEquals('confirmed', $apt['status']);
        }
    }
}
