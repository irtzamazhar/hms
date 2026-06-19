<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientWebTest extends TestCase
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

    public function test_patients_index_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('patients.index'))
            ->assertOk();
    }

    public function test_guests_are_redirected_from_patients_index(): void
    {
        $this->get(route('patients.index'))
            ->assertRedirect(route('login'));
    }

    public function test_patient_create_form_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('patients.create'))
            ->assertOk();
    }

    public function test_can_store_a_new_patient(): void
    {
        $response = $this->actingAs($this->admin)->post(route('patients.store'), [
            'name'   => 'Sara Ahmed',
            'phone'  => '03001234567',
            'gender' => 'female',
            'age'    => 28,
            'age_unit' => 'years',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('patients', ['name' => 'Sara Ahmed', 'phone' => '03001234567']);
    }

    public function test_patient_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('patients.store'), [])
            ->assertSessionHasErrors(['name', 'gender']);
    }

    public function test_can_view_patient_detail(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('patients.show', $patient))
            ->assertOk()
            ->assertSee($patient->name);
    }

    public function test_can_update_patient(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('patients.update', $patient), array_merge($patient->toArray(), [
                'name'   => 'New Name',
                'phone'  => $patient->phone,
                'gender' => $patient->gender,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'name' => 'New Name']);
    }

    public function test_can_delete_patient(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('patients.destroy', $patient))
            ->assertRedirect(route('patients.index'));

        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    }
}
