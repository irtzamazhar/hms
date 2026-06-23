<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /** Roles that must never be deleted or stripped of permissions. */
    private const PROTECTED_ROLES = ['super_admin'];

    public function index(Request $request): View
    {
        $this->authorize('view roles');

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('create roles');

        return view('roles.create', [
            'groupedPermissions' => $this->groupedPermissions(),
            'assigned' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create roles');

        $data = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name|regex:/^[a-z0-9_]+$/',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.regex' => 'Use lowercase letters, numbers and underscores only (e.g. ward_manager).',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.show', $role)->with('success', "Role “{$role->name}” created.");
    }

    public function show(Role $role): View
    {
        $this->authorize('view roles');

        $role->load('permissions', 'users');

        return view('roles.show', [
            'role' => $role,
            'groupedPermissions' => $this->groupedPermissions(),
            'assigned' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function edit(Role $role): View
    {
        $this->authorize('edit roles');

        return view('roles.edit', [
            'role' => $role,
            'groupedPermissions' => $this->groupedPermissions(),
            'assigned' => $role->permissions->pluck('name')->all(),
            'locked' => $this->isProtected($role),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('edit roles');

        $data = $request->validate([
            'name' => "required|string|max:50|regex:/^[a-z0-9_]+$/|unique:roles,name,{$role->id}",
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.regex' => 'Use lowercase letters, numbers and underscores only (e.g. ward_manager).',
        ]);

        // The super_admin role keeps every permission and its name, always.
        if ($this->isProtected($role)) {
            $role->syncPermissions(Permission::pluck('name')->all());

            return redirect()->route('roles.show', $role)
                ->with('success', "“{$role->name}” is a protected role and always has full access.");
        }

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.show', $role)->with('success', "Role “{$role->name}” updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete roles');

        if ($this->isProtected($role)) {
            return back()->withErrors(['error' => "The “{$role->name}” role is protected and cannot be deleted."]);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['error' => "Cannot delete “{$role->name}” — it is still assigned to one or more users."]);
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role “{$name}” deleted.");
    }

    /**
     * Build the display matrix: catalogued groups first (in defined order),
     * then any runtime-created permissions under an "Other" group.
     *
     * @return array<string, Collection>
     */
    private function groupedPermissions(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return Permissions::grouped(Permission::orderBy('name')->get());
    }

    private function isProtected(Role $role): bool
    {
        return in_array($role->name, self::PROTECTED_ROLES, true);
    }
}
