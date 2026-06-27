<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\Patient;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Proves the BelongsToTenant trait: row-level isolation + auto-fill once a
 * tenant is active. (In production the tenant is set by the resolution
 * middleware; here we set it explicitly.)
 */
class HospitalScopeTest extends TestCase
{
    use RefreshDatabase;

    private Hospital $a;

    private Hospital $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->a = Hospital::startTrial(['name' => 'Hosp A', 'slug' => 'hosp-a']);
        $this->b = Hospital::startTrial(['name' => 'Hosp B', 'slug' => 'hosp-b']);
    }

    protected function tearDown(): void
    {
        Tenancy::forget(); // static context must not leak into other tests
        parent::tearDown();
    }

    public function test_create_auto_fills_current_tenant(): void
    {
        Tenancy::set($this->a);
        $p = Patient::factory()->create();

        $this->assertSame($this->a->id, $p->hospital_id);
    }

    public function test_global_scope_isolates_each_tenant(): void
    {
        Tenancy::runFor($this->a, fn () => Patient::factory()->count(2)->create());
        Tenancy::runFor($this->b, fn () => Patient::factory()->count(3)->create());

        Tenancy::set($this->a);
        $this->assertSame(2, Patient::count());
        $this->assertTrue(Patient::get()->every(fn ($p) => $p->hospital_id === $this->a->id));

        Tenancy::set($this->b);
        $this->assertSame(3, Patient::count());

        // No active tenant -> no scope -> everything visible (e.g. super-admin tooling).
        Tenancy::forget();
        $this->assertSame(5, Patient::count());
    }

    public function test_one_tenant_cannot_fetch_another_tenants_record(): void
    {
        $foreign = Tenancy::runFor($this->b, fn () => Patient::factory()->create());

        Tenancy::set($this->a);
        $this->assertNull(Patient::find($foreign->id));               // scoped find misses it
        $this->assertNotNull(Patient::withoutGlobalScopes()->find($foreign->id)); // still in DB
    }

    public function test_explicit_hospital_id_is_not_overwritten(): void
    {
        Tenancy::set($this->a);
        $p = Patient::factory()->create(['hospital_id' => $this->b->id]);

        $this->assertSame($this->b->id, $p->hospital_id);
    }
}
