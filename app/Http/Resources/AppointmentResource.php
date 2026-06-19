<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'appointment_number'   => $this->appointment_number,
            'patient'              => [
                'id'        => $this->patient?->id,
                'name'      => $this->patient?->name,
                'mr_number' => $this->patient?->mr_number,
                'phone'     => $this->patient?->phone,
            ],
            'doctor'               => [
                'id'   => $this->doctor?->id,
                'name' => $this->doctor?->user?->name,
            ],
            'department'           => $this->department?->name,
            'appointment_datetime' => $this->appointment_datetime?->toDateTimeString(),
            'duration_minutes'     => $this->duration_minutes,
            'type'                 => $this->type,
            'reason'               => $this->reason,
            'fee'                  => $this->fee,
            'status'               => $this->status,
            'payment_status'       => $this->payment_status,
            'notes'                => $this->notes,
            'created_at'           => $this->created_at->toDateTimeString(),
        ];
    }
}
