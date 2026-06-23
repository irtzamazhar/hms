<?php

namespace App\Services;

use App\Models\LabBooking;
use App\Models\LabBookingItem;
use App\Models\LabReport;
use App\Models\LabTest;
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
            // Prices are sourced authoritatively from the lab_tests table — never trusted from the client.
            $tests    = LabTest::whereIn('id', $data['tests'])->get();
            $discount = (float) ($data['discount'] ?? 0);
            $total    = (float) $tests->sum('cost');

            $booking = LabBooking::create([
                'booking_number' => LabBooking::generateNumber(),
                'patient_id'     => $data['patient_id'],
                'doctor_id'      => $data['doctor_id'] ?? null,
                'booking_date'   => today(),
                'shift'          => $this->currentShift(),
                'total_amount'   => $total,
                'discount'       => $discount,
                'net_amount'     => max($total - $discount, 0),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'created_by'     => auth()->id(),
            ]);

            foreach ($tests as $test) {
                $item = $booking->items()->create([
                    'test_id'  => $test->id,
                    'cost'     => $test->cost,
                    'discount' => 0,
                    'net_cost' => $test->cost,
                ]);

                LabReport::create([
                    'booking_id'      => $booking->id,
                    'booking_item_id' => $item->id,
                    'test_id'         => $test->id,
                    'patient_id'      => $booking->patient_id,
                ]);
            }

            return $booking;
        });
    }

    private function currentShift(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour >= 8 && $hour < 14  => 'morning',
            $hour >= 14 && $hour < 20 => 'evening',
            default                   => 'night',
        };
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
