<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                       => 'required|string|max:255',
            'cnic'                       => 'nullable|string|max:20',
            'phone'                      => 'nullable|string|max:20',
            'email'                      => 'nullable|email|max:255',
            'gender'                     => 'required|in:male,female,other',
            'dob'                        => 'nullable|date|before:today',
            'age'                        => 'nullable|integer|min:0|max:150',
            'blood_group'                => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-,unknown',
            'address'                    => 'nullable|string',
            'city'                       => 'nullable|string|max:100',
            'emergency_contact_name'     => 'nullable|string|max:255',
            'emergency_contact_phone'    => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'allergies'                  => 'nullable|string',
            'medical_history'            => 'nullable|string',
            'referred_by'                => 'nullable|string|max:255',
            'status'                     => 'nullable|in:active,inactive',
        ];
    }
}
