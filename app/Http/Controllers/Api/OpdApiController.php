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
            'patient_id'     => 'required|exists:patients,id',
            'doctor_id'      => 'required|exists:doctors,id',
            'department_id'  => 'nullable|exists:departments,id',
            'visit_date'     => 'required|date',
            'shift'          => 'required|in:morning,afternoon,evening,night',
            'complaint'      => 'nullable|string',
            'fee'            => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'payment_status' => 'nullable|in:paid,pending,partial',
        ]);

        $netAmount = ($data['fee'] ?? 0) - ($data['discount'] ?? 0);
        $visit = OpdVisit::create(array_merge($data, [
            'visit_number' => OpdVisit::generateVisitNumber(),
            'net_amount'   => $netAmount,
            'status'       => 'waiting',
            'created_by'   => $request->user()->id,
        ]));

        $visit->load(['patient', 'doctor.user', 'department']);

        return (new OpdVisitResource($visit))
            ->response()
            ->setStatusCode(201);
    }
}
