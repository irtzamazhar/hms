<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view departments');
        $departments = Department::with(['headDoctor:id,name'])
            ->withCount(['doctors', 'staff'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->get();

        return view('departments.index', compact('departments'));
    }

    public function create(): View
    {
        $this->authorize('create departments');
        $doctors = Doctor::with('user:id,name')->where('status', 'active')->get();

        return view('departments.create', compact('doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create departments');
        $request->validate([
            'name'           => 'required|string|max:100|unique:departments,name',
            'code'           => 'required|string|max:20|unique:departments,code',
            'head_doctor_id' => 'nullable|exists:users,id',
            'description'    => 'nullable|string',
            'status'         => 'required|in:active,inactive',
        ]);

        Department::create($request->only(['name', 'code', 'head_doctor_id', 'description', 'status']));

        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function show(Department $department): RedirectResponse
    {
        return redirect()->route('departments.index');
    }

    public function edit(Department $department): View
    {
        $this->authorize('edit departments');
        $doctors = Doctor::with('user:id,name')->where('status', 'active')->get();

        return view('departments.edit', compact('department', 'doctors'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('edit departments');
        $request->validate([
            'name'           => "required|string|max:100|unique:departments,name,{$department->id}",
            'head_doctor_id' => 'nullable|exists:users,id',
            'description'    => 'nullable|string',
            'status'         => 'required|in:active,inactive',
        ]);

        $department->update($request->only(['name', 'head_doctor_id', 'description', 'status']));

        return redirect()->route('departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete departments');

        if ($department->doctors()->exists() || $department->staff()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete department with assigned doctors or staff.']);
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}
