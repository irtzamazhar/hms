<?php

namespace Tests\Feature;

use App\Models\SalaryPayment;
use App\Models\SalaryStructure;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryGenerateTest extends TestCase
{
    use RefreshDatabase;

    public function test_generating_salary_creates_pending_payment(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $employee = User::factory()->create();
        SalaryStructure::create([
            'user_id' => $employee->id,
            'basic_salary' => 80000,
            'effective_from' => now()->startOfYear()->toDateString(),
            'is_current' => true,
        ]);

        $this->actingAs($admin)->post(route('salaries.generate.run'), [
            'month' => 6,
            'year' => 2026,
            'user_ids' => [$employee->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('salary_payments', [
            'user_id' => $employee->id,
            'month' => 6,
            'year' => 2026,
            'status' => 'pending',
        ]);

        $this->assertSame('pending', SalaryPayment::first()->status);
    }
}
