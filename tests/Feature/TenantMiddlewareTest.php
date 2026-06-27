<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use App\Support\Tenancy;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end checks for tenant resolution + subscription gating over HTTP.
 */
class TenantMiddlewareTest extends TestCase
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

    private function adminFor(Hospital $h): User
    {
        $u = User::factory()->create(['hospital_id' => $h->id]);
        $u->assignRole('super_admin');

        return $u;
    }

    public function test_request_is_scoped_to_the_users_hospital(): void
    {
        $a = Hospital::startTrial(['name' => 'Hosp A', 'slug' => 'hosp-a']);
        $b = Hospital::startTrial(['name' => 'Hosp B', 'slug' => 'hosp-b']);

        Patient::factory()->create(['hospital_id' => $a->id, 'name' => 'Alice Anderson']);
        Patient::factory()->create(['hospital_id' => $b->id, 'name' => 'Bob Brown']);

        $this->actingAs($this->adminFor($a))
            ->get(route('patients.index'))
            ->assertOk()
            ->assertSee('Alice Anderson')
            ->assertDontSee('Bob Brown');
    }

    public function test_expired_trial_tenant_is_redirected_to_subscription_notice(): void
    {
        $expired = Hospital::create([
            'name' => 'Lapsed', 'slug' => 'lapsed', 'status' => 'active',
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($this->adminFor($expired))
            ->get(route('dashboard'))
            ->assertRedirect(route('subscription.expired'));
    }

    public function test_active_tenant_reaches_the_app(): void
    {
        $h = Hospital::startTrial(['name' => 'Live', 'slug' => 'live']);

        $this->actingAs($this->adminFor($h))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_subscription_notice_is_reachable_while_gated(): void
    {
        $expired = Hospital::create([
            'name' => 'Lapsed2', 'slug' => 'lapsed2', 'status' => 'active',
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($this->adminFor($expired))
            ->get(route('subscription.expired'))
            ->assertOk()
            ->assertSee('Subscription required');
    }

    public function test_user_without_hospital_is_not_gated(): void
    {
        $u = User::factory()->create(['hospital_id' => null]);
        $u->assignRole('super_admin');

        $this->actingAs($u)->get(route('dashboard'))->assertOk();
    }
}
