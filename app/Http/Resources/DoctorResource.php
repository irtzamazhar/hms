<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->user?->name,
            'email'          => $this->user?->email,
            'phone'          => $this->user?->phone,
            'specialization' => $this->specialization,
            'qualification'  => $this->qualification,
            'department'     => $this->department?->name,
            'status'         => $this->status,
            'avatar_url'     => $this->user?->avatar_url,
        ];
    }
}
