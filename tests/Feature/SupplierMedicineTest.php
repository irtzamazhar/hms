<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierMedicineTest extends TestCase
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

    public function test_supplier_permissions_exist(): void
    {
        $this->assertDatabaseHas('permissions', ['name' => 'view suppliers']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage suppliers']);
    }

    public function test_suppliers_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->admin)
            ->get(route('suppliers.index'))
            ->assertOk();
    }

    public function test_user_without_permission_cannot_view_suppliers(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist'); // no supplier permissions

        $this->actingAs($user)
            ->get(route('suppliers.index'))
            ->assertForbidden();
    }

    public function test_medicine_create_page_lists_suppliers(): void
    {
        $supplier = Supplier::create([
            'name' => 'Acme Pharma', 'phone' => '0300', 'status' => 'active',
        ]);

        $this->actingAs($this->admin)
            ->get(route('medicines.create'))
            ->assertOk()
            ->assertSee('Supplier')
            ->assertSee('Acme Pharma');
    }

    public function test_medicine_can_be_linked_to_supplier_on_create(): void
    {
        $supplier = Supplier::create([
            'name' => 'Acme Pharma', 'phone' => '0300', 'status' => 'active',
        ]);

        $this->actingAs($this->admin)->post(route('medicines.store'), [
            'name' => 'Paracetamol',
            'unit' => 'tablet',
            'sale_price' => 10,
            'purchase_price' => 6,
            'supplier_id' => $supplier->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('medicines', [
            'name' => 'Paracetamol',
            'supplier_id' => $supplier->id,
        ]);
    }

    public function test_medicine_supplier_relation_resolves(): void
    {
        $supplier = Supplier::create(['name' => 'Acme', 'phone' => '0300', 'status' => 'active']);
        $medicine = Medicine::create([
            'name' => 'Ibuprofen', 'unit' => 'tablet', 'sale_price' => 5,
            'purchase_price' => 3, 'supplier_id' => $supplier->id,
        ]);

        $this->assertSame('Acme', $medicine->supplier->name);
    }

    public function test_invalid_supplier_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('medicines.store'), [
            'name' => 'BadMed',
            'unit' => 'tablet',
            'sale_price' => 10,
            'purchase_price' => 6,
            'supplier_id' => 99999,
        ])->assertSessionHasErrors('supplier_id');

        $this->assertDatabaseMissing('medicines', ['name' => 'BadMed']);
    }
}
