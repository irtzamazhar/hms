<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpdVisitResource;
use App\Models\OpdVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OpdApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('view opd');

        $visits = OpdVisit::with(['patient:id,name,mr_number', 'doctor.user:id,name', 'department:id,name'])
            ->when($request->date, fn ($q, $d) => $q->whereDate('visit_date', $d))
            ->when($request->doctor_id, fn ($q, $id) => $q->where('doctor_id', $id))
            ->when($request->shift, fn ($q, $s) => $q->where('shift', $s))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->whereHas('patient', fn ($p) => $p->where('name', 'like', "%$s%")->orWhere('mr_number', 'like', "%$s%")))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return OpdVisitResource::collection($visits);
    }

    public function show(OpdVisit $opd): OpdVisitResource
    {
        $this->authorize('view opd');
        $opd->load(['patient', 'doctor.user', 'department']);

        return new OpdVisitResource($opd);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create opd');

        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'nullable|exists:departments,id',
            'visit_date' => 'required|date',
            'shift' => 'required|in:morning,evening,night',
            'complaint' => 'nullable|string',
            'fee' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,insurance',
            'payment_status' => 'nullable|in:pending,paid,partial,waived',
        ]);

        $netAmount = ($data['fee'] ?? 0) - ($data['discount'] ?? 0);
        $visit = OpdVisit::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'department_id' => $data['department_id'] ?? null,
            'visit_date' => $data['visit_date'],
            'shift' => $data['shift'],
            // Map API field names to the real columns (chief_complaints / consultation_fee).
            'chief_complaints' => $data['complaint'] ?? null,
            'consultation_fee' => $data['fee'],
            'discount' => $data['discount'] ?? 0,
            'payment_method' => $data['payment_method'] ?? null,
            'payment_status' => $data['payment_status'] ?? 'pending',
            'net_amount' => $netAmount,
            'visit_number' => OpdVisit::generateVisitNumber(),
            'status' => 'waiting',
            'created_by' => $request->user()->id,
        ]);

        $visit->load(['patient', 'doctor.user', 'department']);

        return (new OpdVisitResource($visit))
            ->response()
            ->setStatusCode(201);
    }
}
