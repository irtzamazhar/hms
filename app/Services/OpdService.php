<?php

namespace App\Services;

use App\Models\OpdVisit;
use App\Models\Prescription;
use Illuminate\Pagination\LengthAwarePaginator;

class OpdService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return OpdVisit::with(['patient:id,name,mr_number', 'doctor.user' => fn ($q) => $q->withTrashed()->select('id', 'name'), 'department:id,name'])
            ->when($filters['date'] ?? null, fn ($q, $d) => $q->whereDate('visit_date', $d))
            ->when($filters['shift'] ?? null, fn ($q, $s) => $q->where('shift', $s))
            ->when($filters['doctor_id'] ?? null, fn ($q, $id) => $q->where('doctor_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('patient', fn ($p) => $p->search($s)))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $data): OpdVisit
    {
        $data['visit_number'] = OpdVisit::generateVisitNumber();
        $data['created_by']   = auth()->id();
        $data['net_amount']   = ($data['consultation_fee'] ?? 0) - ($data['discount'] ?? 0);

        $visit = OpdVisit::create($data);

        if (!empty($data['prescription_items'])) {
            $this->savePrescription($visit, $data['prescription_items']);
        }

        return $visit;
    }

    public function update(OpdVisit $visit, array $data): OpdVisit
    {
        $data['net_amount'] = ($data['consultation_fee'] ?? $visit->consultation_fee)
            - ($data['discount'] ?? $visit->discount);

        $visit->update($data);

        if (!empty($data['prescription_items'])) {
            $visit->prescriptions()->delete();
            $this->savePrescription($visit, $data['prescription_items']);
        }

        return $visit;
    }

    public function currentShift(): string
    {
        $hour = (int) now()->format('H');
        if ($hour >= 8 && $hour < 14) return 'morning';
        if ($hour >= 14 && $hour < 20) return 'evening';
        return 'night';
    }

    private function savePrescription(OpdVisit $visit, array $items): void
    {
        $prescription = Prescription::create([
            'opd_visit_id'      => $visit->id,
            'patient_id'        => $visit->patient_id,
            'doctor_id'         => $visit->doctor_id,
            'prescription_date' => $visit->visit_date,
        ]);

        $prescription->items()->createMany($items);
    }
}
