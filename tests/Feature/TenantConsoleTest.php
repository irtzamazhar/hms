<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\User;
use App\Support\Tenancy;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantConsoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);
    }

    protected function tearDown(): void
    {
        Tenancy::forget();
        parent::tearDown();
    }

    private function platformAdmin(): User
    {
        $u = User::factory()->create(['hospital_id' => null]);
        $u->assignRole('super_admin');

        return $u;
    }

    public function test_platform_admin_can_view_console(): void
    {
        $this->actingAs($this->platformAdmin())
            ->get(route('tenants.index'))
            ->assertOk();
    }

    public function test_tenant_scoped_super_admin_cannot_access_console(): void
    {
        $h = Hospital::startTrial(['name' => 'H', 'slug' => 'h']);
        $u = User::factory()->create(['hospital_id' => $h->id]);
        $u->assignRole('super_admin'); // has 'manage tenants' but belongs to a hospital

        $this->actingAs($u)->get(route('tenants.index'))->assertForbidden();
    }

    public function test_provision_tenant_creates_hospital_and_first_admin(): void
    {
        $this->actingAs($this->platformAdmin())
            ->post(route('tenants.store'), [
                'name' => 'City Hospital',
                'slug' => 'cityhospital',
                'admin_name' => 'City Admin',
                'admin_email' => 'admin@city.test',
                'admin_password' => 'password123',
                'admin_password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('tenants.index'));

        $hospital = Hospital::where('slug', 'cityhospital')->first();
        $this->assertNotNull($hospital);
        $this->assertTrue($hospital->onTrial());
        $this->assertDatabaseHas('users', ['email' => 'admin@city.test', 'hospital_id' => $hospital->id]);
    }

    public function test_mark_subscribed_grants_access_after_trial(): void
    {
        $h = Hospital::create([
            'name' => 'Paid', 'slug' => 'paid', 'status' => 'active',
            'subscription_status' => 'trialing', 'trial_ends_at' => now()->subDay()->toDateString(),
        ]);
        $this->assertFalse($h->hasAccess());

        $this->actingAs($this->platformAdmin())
            ->patch(route('tenants.subscribe', $h), ['subscribed_until' => now()->addYear()->toDateString()])
            ->assertRedirect();

        $this->assertTrue($h->refresh()->hasAccess());
        $this->assertSame('active', $h->subscription_status);
    }

    public function test_suspend_blocks_access(): void
    {
        $h = Hospital::startTrial(['name' => 'Susp', 'slug' => 'susp']);

        $this->actingAs($this->platformAdmin())
            ->patch(route('tenants.status', $h), ['status' => 'suspended'])
            ->assertRedirect();

        $this->assertFalse($h->refresh()->hasAccess());
    }
}
