<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpdVisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'visit_number'   => $this->visit_number,
            'patient'        => [
                'id'        => $this->patient?->id,
                'name'      => $this->patient?->name,
                'mr_number' => $this->patient?->mr_number,
            ],
            'doctor'         => [
                'id'   => $this->doctor?->id,
                'name' => $this->doctor?->user?->name,
            ],
            'department'     => $this->department?->name,
            'visit_date'     => $this->visit_date?->toDateString(),
            'shift'          => $this->shift,
            'complaint'      => $this->complaint,
            'diagnosis'      => $this->diagnosis,
            'fee'            => $this->fee,
            'discount'       => $this->discount,
            'net_amount'     => $this->net_amount,
            'payment_status' => $this->payment_status,
            'status'         => $this->status,
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}
