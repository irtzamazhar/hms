<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_storing_a_purchase_increments_medicine_stock(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $supplier = Supplier::create(['name' => 'Acme', 'phone' => '0300', 'status' => 'active']);
        $medicine = Medicine::create([
            'name' => 'Paracetamol', 'unit' => 'tablet',
            'sale_price' => 5, 'purchase_price' => 3, 'stock_quantity' => 0,
        ]);

        $response = $this->actingAs($admin)->post(route('purchases.store'), [
            'supplier_id'    => $supplier->id,
            'purchase_date'  => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount'    => 0,
            'items'          => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity'    => 10,
                    'unit_price'  => 100,
                    'sale_price'  => 120,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchases.index'));

        $medicine->refresh();
        $this->assertSame(10, (int) $medicine->stock_quantity);
        $this->assertEquals(100, (float) $medicine->purchase_price);
        $this->assertEquals(120, (float) $medicine->sale_price);

        $this->assertDatabaseHas('purchases', ['supplier_id' => $supplier->id, 'total_amount' => 1000]);
        $this->assertDatabaseHas('medicine_stocks', [
            'medicine_id' => $medicine->id,
            'type'        => 'in',
            'quantity'    => 10,
        ]);
    }
}
