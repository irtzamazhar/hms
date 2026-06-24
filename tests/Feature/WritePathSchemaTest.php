<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Doctor;
use App\Models\IpdAdmission;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Models\Shift;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WritePathSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    /** Shift type 'custom' is no longer accepted (not in the enum). */
    public function test_shift_rejects_non_enum_type(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('shifts.store'), [
            'name' => 'Graveyard', 'type' => 'custom',
            'start_time' => '20:00', 'end_time' => '04:00', 'status' => 'active',
        ])->assertSessionHasErrors('type');

        $this->actingAs($admin)->post(route('shifts.store'), [
            'name' => 'Morning', 'type' => 'morning',
            'start_time' => '08:00', 'end_time' => '14:00', 'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('shifts', ['name' => 'Morning', 'type' => 'morning']);
    }

    /** OPD API maps fee→consultation_fee and rejects the bad 'afternoon' shift. */
    public function test_opd_api_store_maps_columns_and_validates_shift(): void
    {
        $admin = $this->admin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        Sanctum::actingAs($admin, ['*']);

        // bad shift rejected
        $this->postJson('/api/opd', [
            'patient_id' => $patient->id, 'doctor_id' => $doctor->id,
            'visit_date' => today()->toDateString(), 'shift' => 'afternoon', 'fee' => 500,
        ])->assertStatus(422);

        // valid request persists consultation_fee + chief_complaints
        $this->postJson('/api/opd', [
            'patient_id' => $patient->id, 'doctor_id' => $doctor->id,
            'visit_date' => today()->toDateString(), 'shift' => 'morning',
            'fee' => 500, 'complaint' => 'Fever',
        ])->assertCreated();

        $visit = OpdVisit::first();
        $this->assertEquals(500, (float) $visit->consultation_fee);
        $this->assertSame('Fever', $visit->chief_complaints);
    }

    /** IPD API sets admitted_by (NOT NULL) and uses the correct admission_type enum. */
    public function test_ipd_api_store_sets_admitted_by_and_valid_type(): void
    {
        $admin = $this->admin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $ward = Ward::factory()->create();
        $bed = Bed::factory()->create(['ward_id' => $ward->id, 'status' => 'available']);

        Sanctum::actingAs($admin, ['*']);

        // bad admission_type rejected
        $this->postJson('/api/ipd', [
            'patient_id' => $patient->id, 'doctor_id' => $doctor->id,
            'ward_id' => $ward->id, 'bed_id' => $bed->id,
            'admission_datetime' => now()->toDateTimeString(), 'admission_type' => 'general',
        ])->assertStatus(422);

        // valid request succeeds and populates admitted_by
        $this->postJson('/api/ipd', [
            'patient_id' => $patient->id, 'doctor_id' => $doctor->id,
            'ward_id' => $ward->id, 'bed_id' => $bed->id,
            'admission_datetime' => now()->toDateTimeString(), 'admission_type' => 'elective',
            'admission_diagnosis' => 'Observation',
        ])->assertCreated();

        $admission = IpdAdmission::first();
        $this->assertSame($admin->id, $admission->admitted_by);
        $this->assertSame('Observation', $admission->admission_diagnosis);
        $this->assertSame('occupied', $bed->fresh()->status);
    }
}
