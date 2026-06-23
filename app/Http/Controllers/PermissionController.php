<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('view permissions');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = Permission::withCount('roles')->orderBy('name')->get();

        return view('permissions.index', [
            'grouped' => Permissions::grouped($all),
            'total' => $all->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create permissions');

        $data = $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name|regex:/^[a-z0-9 _-]+$/',
            'group' => ['required', 'string', Rule::in(Permissions::moduleOptions())],
        ], [
            'name.regex' => 'Use lowercase letters, numbers, spaces, hyphens and underscores only (e.g. "view ward billing").',
            'group.required' => 'Choose the module this permission belongs to.',
        ]);

        Permission::create([
            'name' => $data['name'],
            'group' => $data['group'],
            'guard_name' => 'web',
        ]);

        return back()->with('success', "Permission “{$data['name']}” created.");
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->authorize('delete permissions');

        // Guard the permissions this module itself depends on.
        if (in_array($permission->name, Permissions::groups()['Access Control'], true)) {
            return back()->withErrors(['error' => "“{$permission->name}” is a core Access Control permission and cannot be deleted."]);
        }

        $name = $permission->name;
        $permission->delete();

        return back()->with('success', "Permission “{$name}” deleted.");
    }
}
