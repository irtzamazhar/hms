<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view users');
        $users = User::with('roles')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
            ->when($request->role, fn ($q, $r) => $q->role($r))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->user_type, fn ($q, $t) => $q->where('user_type', $t))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $this->authorize('create users');
        $roles = Role::whereNotIn('name', ['superadmin'])->orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create users');
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'user_type' => 'required|in:admin,doctor,staff,receptionist,pharmacist,lab_technician',
            'role' => 'required|exists:roles,name',
            'status' => 'required|in:active,inactive',
        ]);

        $lastUser = User::whereNotNull('employee_id')->latest('id')->value('employee_id');
        $nextNum = $lastUser ? ((int) ltrim(substr($lastUser, 4), '0') + 1) : 1;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'status' => $request->status,
            'employee_id' => 'EMP-'.str_pad($nextNum, 4, '0', STR_PAD_LEFT),
            'joining_date' => now()->toDateString(),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view users');
        $user->load(['roles', 'doctor.department', 'staff.department', 'salaryStructure']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('edit users');
        $roles = Role::whereNotIn('name', ['superadmin'])->orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('edit users');
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => "required|email|unique:users,email,{$user->id}",
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update(array_filter([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
            'password' => $request->filled('password') ? Hash::make($request->password) : null,
        ]));

        $user->syncRoles([$request->role]);

        return redirect()->route('users.show', $user)->with('success', 'User updated.');
    }

    /**
     * Manage a single user's direct (per-user) permissions, on top of
     * whatever they inherit from their role.
     */
    public function permissions(User $user): View
    {
        $this->authorize('assign user permissions');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return view('users.permissions', [
            'user' => $user,
            'grouped' => Permissions::grouped(Permission::orderBy('name')->get()),
            'direct' => $user->getDirectPermissions()->pluck('name')->all(),
            'viaRole' => $user->getPermissionsViaRoles()->pluck('name')->all(),
        ]);
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $this->authorize('assign user permissions');

        $data = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // syncPermissions only touches DIRECT permissions; role-inherited ones stay intact.
        $user->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('users.permissions', $user)
            ->with('success', "Direct permissions for {$user->name} updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete users');

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
