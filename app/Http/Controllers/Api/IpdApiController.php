<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IpdAdmissionResource;
use App\Models\Bed;
use App\Models\IpdAdmission;
use App\Notifications\IpdAdmissionCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IpdApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('view ipd');

        $admissions = IpdAdmission::with(['patient:id,name,mr_number', 'doctor.user:id,name', 'ward:id,name', 'bed:id,bed_number'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->ward_id, fn ($q, $id) => $q->where('ward_id', $id))
            ->when($request->doctor_id, fn ($q, $id) => $q->where('doctor_id', $id))
            ->when($request->search, fn ($q, $s) => $q->whereHas('patient', fn ($p) => $p->where('name', 'like', "%$s%")->orWhere('mr_number', 'like', "%$s%")))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return IpdAdmissionResource::collection($admissions);
    }

    public function show(IpdAdmission $ipd): IpdAdmissionResource
    {
        $this->authorize('view ipd');
        $ipd->load(['patient', 'doctor.user', 'ward', 'bed']);

        return new IpdAdmissionResource($ipd);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create ipd');

        $data = $request->validate([
            'patient_id'         => 'required|exists:patients,id',
            'doctor_id'          => 'required|exists:doctors,id',
            'ward_id'            => 'required|exists:wards,id',
            'bed_id'             => 'required|exists:beds,id',
            'admission_datetime' => 'required|date',
            'admission_type'     => 'required|in:general,emergency,maternity,icu',
            'diagnosis'          => 'nullable|string',
            'referral_source'    => 'nullable|string',
            'notes'              => 'nullable|string',
        ]);

        $bed = Bed::find($data['bed_id']);
        abort_if($bed->status !== 'available', 422, 'The selected bed is not available.');

        $admission = IpdAdmission::create(array_merge($data, [
            'admission_number' => IpdAdmission::generateAdmissionNumber(),
            'status'           => 'admitted',
            'created_by'       => $request->user()->id,
        ]));

        $bed->update(['status' => 'occupied']);
        $admission->load(['patient', 'doctor.user', 'ward', 'bed']);
        $request->user()->notify(new IpdAdmissionCreated($admission));

        return (new IpdAdmissionResource($admission))
            ->response()
            ->setStatusCode(201);
    }
}
