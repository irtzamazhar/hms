<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use App\Support\Modules;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ModuleSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
    }

    public function test_enabled_module_route_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('opd.index'))
            ->assertOk();
    }

    public function test_disabled_module_route_is_blocked(): void
    {
        Module::where('key', 'opd')->update(['enabled' => false]);
        Modules::forget();

        $this->actingAs($this->admin)
            ->get(route('opd.index'))
            ->assertForbidden();
    }

    public function test_disabling_one_module_does_not_affect_others(): void
    {
        Module::where('key', 'opd')->update(['enabled' => false]);
        Modules::forget();

        $this->actingAs($this->admin)->get(route('opd.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('ipd.index'))->assertOk();
    }

    public function test_core_routes_never_blocked_by_module_middleware(): void
    {
        // Even with everything off, settings/dashboard remain reachable.
        Module::query()->update(['enabled' => false]);
        Modules::forget();

        $this->actingAs($this->admin)->get(route('settings.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->admin)->get(route('users.index'))->assertOk();
    }

    public function test_super_admin_can_update_modules(): void
    {
        // Submit a payload that enables only patients + opd.
        $this->actingAs($this->admin)
            ->patch(route('settings.modules'), ['modules' => ['patients', 'opd']])
            ->assertRedirect();

        $this->assertTrue(Module::where('key', 'patients')->value('enabled'));
        $this->assertTrue(Module::where('key', 'opd')->value('enabled'));
        $this->assertFalse(Module::where('key', 'ipd')->value('enabled'));
        $this->assertFalse(Module::where('key', 'laboratory')->value('enabled'));
    }

    public function test_lab_submodule_routes_respect_laboratory_toggle(): void
    {
        Module::where('key', 'laboratory')->update(['enabled' => false]);
        Modules::forget();

        // lab.tests.* is owned by the laboratory module too.
        $this->actingAs($this->admin)->get(route('lab.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('lab.tests.index'))->assertForbidden();
    }

    public function test_non_super_admin_cannot_update_modules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('hospital_admin'); // has manage settings? -> no; has view settings only

        $this->actingAs($admin)
            ->patch(route('settings.modules'), ['modules' => []])
            ->assertForbidden();
    }
}
