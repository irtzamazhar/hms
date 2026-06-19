<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Notifications\AppointmentScheduled;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppointmentApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('view appointments');

        $appointments = Appointment::with(['patient:id,name,mr_number,phone', 'doctor.user:id,name', 'department:id,name'])
            ->when($request->date, fn ($q, $d) => $q->whereDate('appointment_datetime', $d))
            ->when($request->doctor_id, fn ($q, $id) => $q->where('doctor_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->whereHas('patient', fn ($p) => $p->where('name', 'like', "%$s%")))
            ->latest('appointment_datetime')
            ->paginate($request->integer('per_page', 20));

        return AppointmentResource::collection($appointments);
    }

    public function show(Appointment $appointment): AppointmentResource
    {
        $this->authorize('view appointments');
        $appointment->load(['patient', 'doctor.user', 'department']);

        return new AppointmentResource($appointment);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create appointments');

        $data = $request->validate([
            'patient_id'           => 'required|exists:patients,id',
            'doctor_id'            => 'required|exists:doctors,id',
            'department_id'        => 'nullable|exists:departments,id',
            'appointment_datetime' => 'required|date|after:now',
            'duration_minutes'     => 'nullable|integer|min:5|max:180',
            'type'                 => 'required|in:new,follow_up,consultation,emergency',
            'reason'               => 'nullable|string|max:255',
            'fee'                  => 'nullable|numeric|min:0',
        ]);

        $appointment = Appointment::create(array_merge($data, [
            'appointment_number' => Appointment::generateNumber(),
            'status'             => 'scheduled',
            'payment_status'     => 'pending',
            'created_by'         => $request->user()->id,
        ]));

        $appointment->load(['patient', 'doctor.user', 'department']);
        $request->user()->notify(new AppointmentScheduled($appointment));

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }

    public function updateStatus(Request $request, Appointment $appointment): AppointmentResource
    {
        $this->authorize('create appointments');

        $request->validate([
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no_show',
        ]);

        $appointment->update(['status' => $request->status]);

        return new AppointmentResource($appointment->fresh()->load(['patient', 'doctor.user', 'department']));
    }
}
