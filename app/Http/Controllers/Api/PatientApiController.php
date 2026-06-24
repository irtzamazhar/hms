<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('view patients');

        $patients = Patient::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")
                ->orWhere('mr_number', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%"))
            ->when($request->gender, fn ($q, $g) => $q->where('gender', $g))
            ->when($request->blood_group, fn ($q, $b) => $q->where('blood_group', $b))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return PatientResource::collection($patients);
    }

    public function show(Patient $patient): PatientResource
    {
        $this->authorize('view patients');

        return new PatientResource($patient);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create patients');

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'gender' => 'required|in:male,female,other',
            'dob' => 'nullable|date',
            'age' => 'nullable|integer|min:0',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'allergies' => 'nullable|string',
            'medical_history' => 'nullable|string',
        ]);

        $patient = Patient::create(array_merge($data, [
            'mr_number' => Patient::generateMrNumber(),
            'registered_by' => $request->user()->id,
            'status' => 'active',
        ]));

        return (new PatientResource($patient))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Patient $patient): PatientResource
    {
        $this->authorize('edit patients');

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:100',
            'gender' => 'sometimes|in:male,female,other',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'allergies' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $patient->update($data);

        return new PatientResource($patient->fresh());
    }
}
