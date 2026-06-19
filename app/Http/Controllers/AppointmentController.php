<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Notifications\AppointmentScheduled;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view appointments');
        $appointments = Appointment::with(['patient:id,name,mr_number', 'doctor.user:id,name', 'department:id,name'])
            ->when($request->date, fn ($q, $d) => $q->whereDate('appointment_datetime', $d))
            ->when($request->doctor_id, fn ($q, $id) => $q->where('doctor_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->whereHas('patient', fn ($p) => $p->where('name', 'like', "%$s%")))
            ->latest('appointment_datetime')
            ->paginate(20)
            ->withQueryString();

        $doctors     = Doctor::with('user:id,name')->where('status', 'active')->get();
        $todayCount  = Appointment::whereDate('appointment_datetime', today())->count();
        $pendingCount = Appointment::where('status', 'scheduled')->whereDate('appointment_datetime', today())->count();

        return view('appointments.index', compact('appointments', 'doctors', 'todayCount', 'pendingCount'));
    }

    public function create(): View
    {
        $this->authorize('create appointments');
        $patients    = Patient::select('id', 'name', 'mr_number', 'phone')->latest()->limit(100)->get();
        $doctors     = Doctor::with('user:id,name')->where('status', 'active')->get();
        $departments = Department::active()->get();

        return view('appointments.create', compact('patients', 'doctors', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create appointments');
        $request->validate([
            'patient_id'           => 'required|exists:patients,id',
            'doctor_id'            => 'required|exists:doctors,id',
            'department_id'        => 'nullable|exists:departments,id',
            'appointment_datetime' => 'required|date|after:now',
            'duration_minutes'     => 'nullable|integer|min:5|max:180',
            'type'                 => 'required|in:opd,follow_up,emergency,teleconsultation',
            'reason'               => 'nullable|string|max:255',
            'fee'                  => 'nullable|numeric|min:0',
        ]);

        $appointment = Appointment::create(array_merge($request->all(), [
            'appointment_number' => Appointment::generateNumber(),
            'status'             => 'scheduled',
            'payment_status'     => 'pending',
            'created_by'         => auth()->id(),
        ]));

        $appointment->load(['patient', 'doctor.user', 'department']);
        auth()->user()->notify(new AppointmentScheduled($appointment));

        return redirect()->route('appointments.index')->with('success', 'Appointment booked.');
    }

    public function show(Appointment $appointment): View
    {
        $this->authorize('view appointments');
        $appointment->load(['patient', 'doctor.user', 'department', 'createdBy']);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorize('create appointments');
        $patients    = Patient::select('id', 'name', 'mr_number', 'phone')->get();
        $doctors     = Doctor::with('user:id,name')->where('status', 'active')->get();
        $departments = Department::active()->get();

        return view('appointments.edit', compact('appointment', 'patients', 'doctors', 'departments'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('create appointments');
        $request->validate([
            'appointment_datetime' => 'required|date',
            'duration_minutes'     => 'nullable|integer|min:5',
            'type'                 => 'required|in:opd,follow_up,emergency,teleconsultation',
            'status'               => 'required|in:scheduled,confirmed,completed,cancelled,no_show',
            'reason'               => 'nullable|string',
            'notes'                => 'nullable|string',
            'fee'                  => 'nullable|numeric|min:0',
        ]);

        $appointment->update($request->only(['appointment_datetime', 'duration_minutes', 'type', 'status', 'reason', 'notes', 'fee', 'payment_status', 'doctor_id', 'department_id']));

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('create appointments');
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Appointment deleted.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $request->validate(['status' => 'required|in:scheduled,confirmed,completed,cancelled,no_show']);
        $appointment->update(['status' => $request->status]);

        return back()->with('success', 'Status updated.');
    }
}
