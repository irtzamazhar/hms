<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Department;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WardController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view wards');
        $wards = Ward::with(['department:id,name', 'beds'])
            ->withCount('beds')
            ->when($request->department_id, fn ($q, $id) => $q->where('department_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->get();

        $departments = Department::active()->get();

        return view('wards.index', compact('wards', 'departments'));
    }

    public function create(): View
    {
        $this->authorize('create wards');

        return view('wards.create', ['departments' => Department::active()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create wards');
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:20|unique:wards,code',
            'department_id' => 'nullable|exists:departments,id',
            'ward_type'     => 'required|in:general,private,semi_private,icu,emergency,maternity,pediatric',
            'total_beds'    => 'required|integer|min:1|max:200',
            'floor'         => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'status'        => 'required|in:active,inactive',
        ]);

        $ward = Ward::create($validated);

        // Auto-create beds
        for ($i = 1; $i <= $validated['total_beds']; $i++) {
            $ward->beds()->create([
                'bed_number' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'bed_type'   => 'general',
                'charge_per_day' => 0,
                'status'     => 'available',
            ]);
        }

        return redirect()->route('wards.index')->with('success', "Ward '{$ward->name}' created with {$validated['total_beds']} beds.");
    }

    public function show(Ward $ward): RedirectResponse
    {
        return redirect()->route('wards.beds', $ward);
    }

    public function edit(Ward $ward): View
    {
        $this->authorize('edit wards');

        return view('wards.edit', ['ward' => $ward, 'departments' => Department::active()->get()]);
    }

    public function update(Request $request, Ward $ward): RedirectResponse
    {
        $this->authorize('edit wards');
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'ward_type'     => 'required|in:general,private,semi_private,icu,emergency,maternity,pediatric',
            'floor'         => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'status'        => 'required|in:active,inactive',
        ]);

        $ward->update($validated);

        return redirect()->route('wards.index')->with('success', 'Ward updated.');
    }

    public function destroy(Ward $ward): RedirectResponse
    {
        $this->authorize('delete wards');

        if ($ward->beds()->where('status', 'occupied')->exists()) {
            return back()->withErrors(['error' => 'Cannot delete ward with occupied beds.']);
        }

        $ward->beds()->delete();
        $ward->delete();

        return redirect()->route('wards.index')->with('success', 'Ward deleted.');
    }

    public function beds(Ward $ward): View
    {
        $this->authorize('view wards');
        $ward->load('beds');

        return view('wards.beds', compact('ward'));
    }
}
