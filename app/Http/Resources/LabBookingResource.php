<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->name,
                'mr_number' => $this->patient?->mr_number,
            ],
            'referred_by' => $this->doctor?->user?->name,
            'booking_date' => $this->booking_date?->toDateString(),
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'discount' => $this->discount,
            'net_amount' => $this->net_amount,
            'payment_status' => $this->payment_status,
            'tests_count' => $this->items_count ?? $this->items?->count(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
