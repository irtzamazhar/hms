<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view doctors');
        $doctors = Doctor::with(['user:id,name,email,status', 'department:id,name'])
            ->when($request->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$request->search}%")))
            ->when($request->department_id, fn ($q, $id) => $q->where('department_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $departments = Department::active()->get();

        return view('doctors.index', compact('doctors', 'departments'));
    }

    public function create(): View
    {
        $this->authorize('create doctors');

        return view('doctors.create', ['departments' => Department::active()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create doctors');
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'phone'            => 'nullable|string|max:20',
            'qualification'    => 'required|string|max:255',
            'specialization'   => 'required|string|max:255',
            'department_id'    => 'nullable|exists:departments,id',
            'cnic'             => 'nullable|string|max:20|unique:doctors,cnic',
            'consultation_fee' => 'nullable|numeric|min:0',
            'available_days'   => 'nullable|array',
            'bio'              => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $lastDoctor = Doctor::latest('id')->value('doctor_id');
            $nextNum    = $lastDoctor ? ((int) ltrim(substr($lastDoctor, 4), '0') + 1) : 1;

            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make('Doctor@123'),
                'phone'      => $request->phone,
                'user_type'  => 'doctor',
                'joining_date' => now(),
                'employee_id'  => 'EMP-' . str_pad($nextNum + 100, 4, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('doctor');

            Doctor::create([
                'user_id'          => $user->id,
                'doctor_id'        => 'DOC-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT),
                'department_id'    => $request->department_id,
                'qualification'    => $request->qualification,
                'specialization'   => $request->specialization,
                'cnic'             => $request->cnic,
                'phone'            => $request->phone,
                'consultation_fee' => $request->consultation_fee ?? 0,
                'bio'              => $request->bio,
                'available_days'   => $request->available_days,
            ]);
        });

        return redirect()->route('doctors.index')->with('success', 'Doctor added. Default password: Doctor@123');
    }

    public function show(Doctor $doctor): View
    {
        $this->authorize('view doctors');
        $doctor->load(['user', 'department']);
        $stats = [
            'total_patients' => $doctor->opdVisits()->distinct('patient_id')->count('patient_id'),
            'today_opd'      => $doctor->opdVisits()->whereDate('visit_date', today())->count(),
            'month_revenue'  => $doctor->opdVisits()->whereMonth('visit_date', now()->month)->sum('net_amount'),
        ];

        return view('doctors.show', compact('doctor', 'stats'));
    }

    public function edit(Doctor $doctor): View
    {
        $this->authorize('edit doctors');

        return view('doctors.edit', compact('doctor', ...['departments' => Department::active()->get()]));
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->authorize('edit doctors');
        $request->validate([
            'qualification'    => 'required|string',
            'specialization'   => 'required|string',
            'department_id'    => 'nullable|exists:departments,id',
            'consultation_fee' => 'nullable|numeric|min:0',
            'status'           => 'required|in:active,inactive,on_leave',
        ]);

        $doctor->update($request->only(['qualification', 'specialization', 'department_id', 'cnic', 'phone', 'consultation_fee', 'bio', 'available_days', 'available_from', 'available_to', 'status']));
        $doctor->user->update($request->only(['name', 'phone']));

        return redirect()->route('doctors.show', $doctor)->with('success', 'Doctor updated.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->authorize('delete doctors');
        $doctor->user->delete();

        return redirect()->route('doctors.index')->with('success', 'Doctor removed.');
    }
}
