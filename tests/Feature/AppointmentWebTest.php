<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentWebTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
    }

    public function test_appointments_index_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('appointments.index'))
            ->assertOk();
    }

    public function test_can_create_appointment(): void
    {
        Notification::fake();

        $patient = Patient::factory()->create();
        $doctor  = Doctor::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('appointments.store'), [
            'patient_id'           => $patient->id,
            'doctor_id'            => $doctor->id,
            'appointment_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'type'                 => 'opd',
            'fee'                  => 1000,
        ]);

        $response->assertRedirect(route('appointments.index'));
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
            'status'     => 'scheduled',
        ]);
    }

    public function test_appointment_creation_sends_notification(): void
    {
        Notification::fake();

        $patient = Patient::factory()->create();
        $doctor  = Doctor::factory()->create();

        $this->actingAs($this->admin)->post(route('appointments.store'), [
            'patient_id'           => $patient->id,
            'doctor_id'            => $doctor->id,
            'appointment_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'type'                 => 'opd',
            'fee'                  => 1000,
        ]);

        Notification::assertSentTo(
            $this->admin,
            \App\Notifications\AppointmentScheduled::class
        );
    }

    public function test_can_update_appointment_status(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('appointments.status', $appointment), ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'confirmed']);
    }

    public function test_can_delete_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('appointments.destroy', $appointment))
            ->assertRedirect();

        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id, 'deleted_at' => null]);
    }
}
