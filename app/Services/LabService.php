<?php

namespace App\Services;

use App\Models\LabBooking;
use App\Models\LabBookingItem;
use App\Models\LabReport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LabService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return LabBooking::with(['patient:id,name,mr_number', 'doctor.user:id,name'])
            ->when($filters['date'] ?? null, fn ($q, $d) => $q->whereDate('booking_date', $d))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('patient', fn ($p) => $p->search($s)))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function createBooking(array $data): LabBooking
    {
        return DB::transaction(function () use ($data) {
            $data['booking_number'] = LabBooking::generateNumber();
            $data['created_by']     = auth()->id();

            $total = 0;
            foreach ($data['tests'] as $test) {
                $total += $test['cost'];
            }

            $data['total_amount'] = $total;
            $data['net_amount']   = $total - ($data['discount'] ?? 0);

            $booking = LabBooking::create($data);

            foreach ($data['tests'] as $test) {
                $item = $booking->items()->create([
                    'test_id'  => $test['test_id'],
                    'cost'     => $test['cost'],
                    'discount' => $test['discount'] ?? 0,
                    'net_cost' => $test['cost'] - ($test['discount'] ?? 0),
                ]);

                LabReport::create([
                    'booking_id'      => $booking->id,
                    'booking_item_id' => $item->id,
                    'test_id'         => $test['test_id'],
                    'patient_id'      => $booking->patient_id,
                ]);
            }

            return $booking;
        });
    }

    public function saveResults(LabBooking $booking, array $results): void
    {
        DB::transaction(function () use ($booking, $results) {
            foreach ($results as $reportId => $result) {
                LabReport::find($reportId)?->update(array_merge($result, [
                    'result_entered_at' => now(),
                    'technician_id'     => auth()->id(),
                    'status'            => 'completed',
                ]));

                LabBookingItem::where('id', $result['booking_item_id'] ?? null)?->update(['status' => 'completed']);
            }

            $allDone = $booking->reports()->where('status', '!=', 'completed')->doesntExist();
            if ($allDone) {
                $booking->update(['status' => 'completed']);
            }
        });
    }
}
