<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'mr_number'               => $this->mr_number,
            'name'                    => $this->name,
            'phone'                   => $this->phone,
            'email'                   => $this->email,
            'gender'                  => $this->gender,
            'dob'                     => $this->dob?->toDateString(),
            'age'                     => $this->age,
            'blood_group'             => $this->blood_group,
            'address'                 => $this->address,
            'city'                    => $this->city,
            'emergency_contact_name'  => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'allergies'               => $this->allergies,
            'status'                  => $this->status,
            'registered_at'           => $this->created_at->toDateTimeString(),
        ];
    }
}
