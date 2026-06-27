<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Support\Tenancy;
use Database\Seeders\DefaultHospitalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_trial_provisions_a_12_month_trial(): void
    {
        $h = Hospital::startTrial(['name' => 'Trial Hosp', 'slug' => 'trial-hosp']);

        $this->assertSame('trialing', $h->subscription_status);
        $this->assertTrue($h->onTrial());
        $this->assertTrue($h->hasAccess());
        $this->assertEqualsWithDelta(365, $h->trialDaysLeft(), 1);
    }

    public function test_expired_trial_without_subscription_loses_access(): void
    {
        $h = Hospital::create([
            'name' => 'Expired', 'slug' => 'expired', 'status' => 'active',
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->subDay()->toDateString(),
        ]);

        $this->assertTrue($h->trialExpired());
        $this->assertFalse($h->onTrial());
        $this->assertFalse($h->hasAccess());
    }

    public function test_active_subscription_grants_access_after_trial(): void
    {
        $h = Hospital::create([
            'name' => 'Paid', 'slug' => 'paid', 'status' => 'active',
            'subscription_status' => 'active',
            'trial_ends_at' => now()->subMonth()->toDateString(),
            'subscribed_until' => now()->addYear()->toDateString(),
        ]);

        $this->assertTrue($h->subscriptionActive());
        $this->assertTrue($h->hasAccess());
    }

    public function test_suspended_tenant_has_no_access_even_with_subscription(): void
    {
        $h = Hospital::create([
            'name' => 'Suspended', 'slug' => 'suspended', 'status' => 'suspended',
            'subscription_status' => 'active',
            'subscribed_until' => now()->addYear()->toDateString(),
        ]);

        $this->assertFalse($h->hasAccess());
    }

    public function test_tenancy_context_set_and_run_for(): void
    {
        $h = Hospital::startTrial(['name' => 'Ctx', 'slug' => 'ctx']);

        $this->assertNull(Tenancy::current());

        Tenancy::set($h);
        $this->assertSame($h->id, Tenancy::id());
        $this->assertTrue(Tenancy::check());

        Tenancy::forget();
        $this->assertFalse(Tenancy::check());

        $ran = Tenancy::runFor($h, fn () => Tenancy::id());
        $this->assertSame($h->id, $ran);
        $this->assertNull(Tenancy::current()); // restored after callback
    }

    public function test_default_hospital_is_seeded(): void
    {
        $this->seed(DefaultHospitalSeeder::class);

        $this->assertDatabaseHas('hospitals', ['id' => 1, 'slug' => 'default']);
        $this->assertTrue(Hospital::find(1)->hasAccess());
    }
}
