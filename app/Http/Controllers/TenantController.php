<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Platform (vendor) console for managing tenant hospitals and their
 * subscriptions under the manual billing model. Gated by the `platform`
 * middleware (platform admins only).
 */
class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $hospitals = Hospital::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%"))
            ->withCount('users')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('tenants.index', compact('hospitals'));
    }

    public function create(): View
    {
        return view('tenants.create');
    }

    /**
     * Provision a new tenant on a free trial plus its first admin user.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:hospitals,slug'],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $hospital = DB::transaction(function () use ($data) {
            $hospital = Hospital::startTrial([
                'name' => $data['name'],
                'slug' => Str::lower($data['slug']),
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            $admin = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'user_type' => 'super_admin',
                'status' => 'active',
                'hospital_id' => $hospital->id,
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('super_admin');

            return $hospital;
        });

        return redirect()->route('tenants.index')
            ->with('success', "Tenant “{$hospital->name}” provisioned with a free trial until {$hospital->trial_ends_at->toFormattedDateString()}.");
    }

    public function show(Hospital $tenant): View
    {
        $tenant->loadCount('users');

        return view('tenants.show', compact('tenant'));
    }

    /**
     * Manually mark a tenant as subscribed (paid) until a date.
     */
    public function subscribe(Request $request, Hospital $tenant): RedirectResponse
    {
        $data = $request->validate([
            'subscribed_until' => 'required|date|after:today',
            'plan' => 'nullable|string|max:50',
        ]);

        $tenant->update([
            'subscription_status' => 'active',
            'status' => 'active',
            'plan' => $data['plan'] ?? ($tenant->plan === 'trial' ? 'standard' : $tenant->plan),
            'subscribed_until' => $data['subscribed_until'],
        ]);

        return back()->with('success', "{$tenant->name} marked as subscribed until {$tenant->subscribed_until->toFormattedDateString()}.");
    }

    public function extendTrial(Request $request, Hospital $tenant): RedirectResponse
    {
        $data = $request->validate(['days' => 'required|integer|min:1|max:365']);

        $base = $tenant->trial_ends_at && $tenant->trial_ends_at->isFuture() ? $tenant->trial_ends_at : now();
        $tenant->update([
            'subscription_status' => 'trialing',
            'trial_ends_at' => $base->copy()->addDays((int) $data['days'])->toDateString(),
        ]);

        return back()->with('success', "Trial extended to {$tenant->trial_ends_at->toFormattedDateString()}.");
    }

    public function setStatus(Request $request, Hospital $tenant): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'suspended'])]]);
        $tenant->update(['status' => $data['status']]);

        return back()->with('success', "{$tenant->name} is now {$data['status']}.");
    }
}
