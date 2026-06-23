<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlWebTest extends TestCase
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

    public function test_roles_index_is_accessible_to_super_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Roles');
    }

    public function test_user_without_permission_cannot_view_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $this->actingAs($user)
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    public function test_can_create_role_with_permissions(): void
    {
        $this->actingAs($this->admin)->post(route('roles.store'), [
            'name'        => 'ward_manager',
            'permissions' => ['view wards', 'edit wards'],
        ])->assertRedirect();

        $role = Role::where('name', 'ward_manager')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('view wards'));
        $this->assertTrue($role->hasPermissionTo('edit wards'));
        $this->assertFalse($role->hasPermissionTo('delete wards'));
    }

    public function test_role_name_must_be_unique_and_well_formed(): void
    {
        $this->actingAs($this->admin)
            ->post(route('roles.store'), ['name' => 'Bad Name!'])
            ->assertSessionHasErrors('name');

        $this->actingAs($this->admin)
            ->post(route('roles.store'), ['name' => 'super_admin'])
            ->assertSessionHasErrors('name');
    }

    public function test_can_update_role_permissions(): void
    {
        $role = Role::create(['name' => 'temp_role', 'guard_name' => 'web']);
        $role->syncPermissions(['view wards']);

        $this->actingAs($this->admin)->put(route('roles.update', $role), [
            'name'        => 'temp_role',
            'permissions' => ['view patients', 'create patients'],
        ])->assertRedirect();

        $role->refresh();
        $this->assertEqualsCanonicalizing(
            ['view patients', 'create patients'],
            $role->permissions->pluck('name')->all()
        );
    }

    public function test_super_admin_role_is_protected_from_changes(): void
    {
        $role  = Role::where('name', 'super_admin')->first();
        $count = $role->permissions()->count();

        // Attempt to strip all permissions — should be ignored.
        $this->actingAs($this->admin)->put(route('roles.update', $role), [
            'name'        => 'super_admin',
            'permissions' => [],
        ])->assertRedirect();

        $this->assertSame($count, $role->fresh()->permissions()->count());
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $role = Role::where('name', 'super_admin')->first();

        $this->actingAs($this->admin)
            ->delete(route('roles.destroy', $role))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
    }

    public function test_role_in_use_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'in_use', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('in_use');

        $this->actingAs($this->admin)
            ->delete(route('roles.destroy', $role))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('roles', ['name' => 'in_use']);
    }

    public function test_unused_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'disposable', 'guard_name' => 'web']);

        $this->actingAs($this->admin)
            ->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['name' => 'disposable']);
    }

    public function test_can_create_and_delete_permission(): void
    {
        $this->actingAs($this->admin)
            ->post(route('permissions.store'), ['name' => 'view billing', 'group' => 'Reports'])
            ->assertRedirect();

        $this->assertDatabaseHas('permissions', ['name' => 'view billing', 'group' => 'Reports']);

        $perm = Permission::where('name', 'view billing')->first();
        $this->actingAs($this->admin)
            ->delete(route('permissions.destroy', $perm))
            ->assertRedirect();

        $this->assertDatabaseMissing('permissions', ['name' => 'view billing']);
    }

    public function test_permission_requires_a_module_group(): void
    {
        $this->actingAs($this->admin)
            ->post(route('permissions.store'), ['name' => 'view billing'])
            ->assertSessionHasErrors('group');

        $this->assertDatabaseMissing('permissions', ['name' => 'view billing']);
    }

    public function test_core_access_control_permission_cannot_be_deleted(): void
    {
        $perm = Permission::where('name', 'view roles')->first();

        $this->actingAs($this->admin)
            ->delete(route('permissions.destroy', $perm))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('permissions', ['name' => 'view roles']);
    }

    public function test_can_assign_direct_permissions_to_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $this->actingAs($this->admin)->put(route('users.permissions.update', $user), [
            'permissions' => ['view reports', 'export reports'],
        ])->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->hasDirectPermission('view reports'));
        $this->assertTrue($user->hasPermissionTo('export reports'));
        // Role-inherited permissions remain intact.
        $this->assertTrue($user->hasPermissionTo('view patients'));
    }

    public function test_direct_permissions_page_renders(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $this->actingAs($this->admin)
            ->get(route('users.permissions', $user))
            ->assertOk()
            ->assertSee('via role');
    }
}
