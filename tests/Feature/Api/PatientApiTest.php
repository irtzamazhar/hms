<?php

namespace Tests\Feature\Api;

use App\Models\Patient;

class PatientApiTest extends ApiTestCase
{
    public function test_can_list_patients(): void
    {
        Patient::factory()->count(3)->create();

        $this->getJson('/api/patients', $this->asAdmin())
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_list_requires_authentication(): void
    {
        $this->getJson('/api/patients')->assertUnauthorized();
    }

    public function test_can_create_patient(): void
    {
        $response = $this->postJson('/api/patients', [
            'name'   => 'Ahmed Khan',
            'phone'  => '03001234567',
            'gender' => 'male',
            'age'    => 35,
        ], $this->asAdmin());

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Ahmed Khan']);

        $this->assertDatabaseHas('patients', ['name' => 'Ahmed Khan']);
    }

    public function test_create_patient_validates_required_fields(): void
    {
        $this->postJson('/api/patients', [], $this->asAdmin())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'gender']);
    }

    public function test_create_patient_validates_gender_enum(): void
    {
        $this->postJson('/api/patients', [
            'name'   => 'Test',
            'phone'  => '03001234567',
            'gender' => 'unknown',
        ], $this->asAdmin())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('gender');
    }

    public function test_can_show_patient(): void
    {
        $patient = Patient::factory()->create();

        $this->getJson("/api/patients/{$patient->id}", $this->asAdmin())
            ->assertOk()
            ->assertJsonFragment(['id' => $patient->id, 'name' => $patient->name]);
    }

    public function test_show_returns_404_for_missing_patient(): void
    {
        $this->getJson('/api/patients/999999', $this->asAdmin())
            ->assertNotFound();
    }

    public function test_can_update_patient(): void
    {
        $patient = Patient::factory()->create();

        $this->putJson("/api/patients/{$patient->id}", [
            'name' => 'Updated Name',
        ], $this->asAdmin())
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'name' => 'Updated Name']);
    }

    public function test_can_filter_patients_by_gender(): void
    {
        Patient::factory()->create(['gender' => 'male']);
        Patient::factory()->create(['gender' => 'female']);

        $response = $this->getJson('/api/patients?gender=male', $this->asAdmin())
            ->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $patient) {
            $this->assertEquals('male', $patient['gender']);
        }
    }
}
