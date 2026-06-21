<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view staff');
        $staff = Staff::with(['user' => fn ($q) => $q->withTrashed()->select('id', 'name', 'email', 'user_type', 'phone', 'employee_id'), 'department:id,name'])
            ->when($request->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$request->search}%")))
            ->when($request->department_id, fn ($q, $id) => $q->where('department_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20)->withQueryString();

        $departments = Department::active()->get();

        return view('staff.index', compact('staff', 'departments'));
    }

    public function create(): View
    {
        $this->authorize('create staff');

        return view('staff.create', [
            'departments' => Department::active()->get(),
            'userTypes'   => ['nurse', 'receptionist', 'pharmacist', 'lab_technician', 'accountant'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create staff');
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'user_type'   => 'required|in:nurse,receptionist,pharmacist,lab_technician,accountant',
            'designation' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'basic_salary'  => 'nullable|numeric|min:0',
            'cnic'          => 'nullable|string|max:20|unique:staff,cnic',
        ]);

        DB::transaction(function () use ($request) {
            $lastStaff = Staff::latest('id')->value('staff_id');
            $nextNum   = $lastStaff ? ((int) ltrim(substr($lastStaff, 5), '0') + 1) : 1;

            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make('Staff@123'),
                'user_type'  => $request->user_type,
                'joining_date' => now(),
                'employee_id'  => 'EMP-' . str_pad($nextNum + 200, 4, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
            ]);

            $user->assignRole($request->user_type);

            Staff::create([
                'user_id'       => $user->id,
                'staff_id'      => 'STF-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT),
                'department_id' => $request->department_id,
                'designation'   => $request->designation,
                'cnic'          => $request->cnic,
                'phone'         => $request->phone,
                'basic_salary'  => $request->basic_salary ?? 0,
            ]);
        });

        return redirect()->route('staff.index')->with('success', 'Staff member added. Default password: Staff@123');
    }

    public function show(Staff $staff): View
    {
        $this->authorize('view staff');
        $staff->load(['user', 'department']);

        return view('staff.show', compact('staff'));
    }

    public function edit(Staff $staff): View
    {
        $this->authorize('edit staff');

        return view('staff.edit', ['staff' => $staff, 'departments' => Department::active()->get()]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $this->authorize('edit staff');
        $request->validate(['designation' => 'required|string', 'status' => 'required|in:active,inactive,on_leave,terminated']);

        $staff->update($request->only(['department_id', 'designation', 'cnic', 'phone', 'address', 'emergency_contact', 'basic_salary', 'status']));
        $staff->user?->update($request->only(['name', 'phone']));

        return redirect()->route('staff.show', $staff)->with('success', 'Staff updated.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorize('delete staff');
        $staff->loadMissing('user');
        $staff->user?->delete();
        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff member removed.');
    }
}
