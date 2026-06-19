<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IpdAdmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'admission_number'   => $this->admission_number,
            'patient'            => [
                'id'        => $this->patient?->id,
                'name'      => $this->patient?->name,
                'mr_number' => $this->patient?->mr_number,
            ],
            'doctor'             => [
                'id'   => $this->doctor?->id,
                'name' => $this->doctor?->user?->name,
            ],
            'ward'               => $this->ward?->name,
            'bed'                => $this->bed?->bed_number,
            'admission_datetime' => $this->admission_datetime?->toDateTimeString(),
            'discharge_datetime' => $this->discharge_datetime?->toDateTimeString(),
            'admission_type'     => $this->admission_type,
            'diagnosis'          => $this->diagnosis,
            'status'             => $this->status,
            'total_charges'      => $this->total_charges,
            'days_stayed'        => $this->admission_datetime
                ? $this->admission_datetime->diffInDays($this->discharge_datetime ?? now())
                : null,
            'created_at'         => $this->created_at->toDateTimeString(),
        ];
    }
}
