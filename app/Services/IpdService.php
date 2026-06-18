<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\IpdAdmission;
use Illuminate\Pagination\LengthAwarePaginator;

class IpdService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return IpdAdmission::with(['patient:id,name,mr_number', 'doctor.user:id,name', 'ward:id,name', 'bed:id,bed_number'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['ward_id'] ?? null, fn ($q, $id) => $q->where('ward_id', $id))
            ->when($filters['doctor_id'] ?? null, fn ($q, $id) => $q->where('doctor_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('patient', fn ($p) => $p->search($s)))
            ->latest('admission_datetime')
            ->paginate(20)
            ->withQueryString();
    }

    public function admit(array $data): IpdAdmission
    {
        $data['admission_number'] = IpdAdmission::generateAdmissionNumber();
        $data['admitted_by']      = auth()->id();

        $admission = IpdAdmission::create($data);

        if ($admission->bed_id) {
            Bed::find($admission->bed_id)?->update(['status' => 'occupied']);
        }

        return $admission;
    }

    public function discharge(IpdAdmission $admission, array $data): IpdAdmission
    {
        $data['status']          = 'discharged';
        $data['discharged_by']   = auth()->id();
        $data['discharge_datetime'] ??= now();

        $admission->update($data);

        if ($admission->bed_id) {
            Bed::find($admission->bed_id)?->update(['status' => 'available']);
        }

        return $admission;
    }

    public function addTreatment(IpdAdmission $admission, array $data): void
    {
        $data['doctor_id']          = $data['doctor_id'] ?? $admission->doctor_id;
        $data['treatment_datetime'] = $data['treatment_datetime'] ?? now();

        $admission->treatments()->create($data);
    }

    public function calculateCharges(IpdAdmission $admission): array
    {
        $days         = $admission->days_admitted ?: 1;
        $bedCharge    = $days * $admission->daily_bed_charge;
        $total        = $bedCharge + $admission->doctor_charges
            + $admission->nursing_charges + $admission->medicine_charges
            + $admission->lab_charges + $admission->other_charges;

        return [
            'days'       => $days,
            'bed_charge' => $bedCharge,
            'total'      => $total,
            'net'        => $total - $admission->discount,
        ];
    }
}
