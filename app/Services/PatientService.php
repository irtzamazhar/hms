<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;

class PatientService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Patient::query()
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->search($s))
            ->when($filters['gender'] ?? null, fn ($q, $g) => $q->where('gender', $g))
            ->when($filters['blood_group'] ?? null, fn ($q, $b) => $q->where('blood_group', $b))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $data): Patient
    {
        $data['mr_number']    = Patient::generateMrNumber();
        $data['registered_by'] = auth()->id();

        return Patient::create($data);
    }

    public function update(Patient $patient, array $data): Patient
    {
        $patient->update($data);

        return $patient;
    }

    public function getHistory(Patient $patient): array
    {
        return [
            'opd_visits'    => $patient->opdVisits()->with('doctor.user')->latest()->get(),
            'ipd_admissions' => $patient->ipdAdmissions()->with(['doctor.user', 'ward'])->latest()->get(),
            'lab_bookings'  => $patient->labBookings()->with('items.test')->latest()->get(),
            'prescriptions' => $patient->prescriptions()->with(['doctor.user', 'items'])->latest()->get(),
            'sales'         => $patient->sales()->latest()->get(),
        ];
    }
}
