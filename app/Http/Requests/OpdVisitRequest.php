<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpdVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'             => 'required|exists:patients,id',
            'doctor_id'              => 'required|exists:doctors,id',
            'department_id'          => 'nullable|exists:departments,id',
            'visit_date'             => 'required|date',
            'shift'                  => 'required|in:morning,evening,night',
            'chief_complaints'       => 'nullable|string',
            'symptoms'               => 'nullable|string',
            'diagnosis'              => 'nullable|string',
            'treatment'              => 'nullable|string',
            'notes'                  => 'nullable|string',
            'vital_bp'               => 'nullable|string|max:20',
            'vital_pulse'            => 'nullable|string|max:20',
            'vital_temperature'      => 'nullable|string|max:20',
            'vital_weight'           => 'nullable|string|max:20',
            'vital_height'           => 'nullable|string|max:20',
            'vital_spo2'             => 'nullable|string|max:20',
            'consultation_fee'       => 'nullable|numeric|min:0',
            'discount'               => 'nullable|numeric|min:0',
            'payment_status'         => 'nullable|in:pending,paid,partial,waived',
            'payment_method'         => 'nullable|string',
            'is_follow_up'           => 'boolean',
            'follow_up_date'         => 'nullable|date|after:visit_date',
            'status'                 => 'nullable|in:waiting,in_progress,completed,cancelled',
            'prescription_items'     => 'nullable|array',
            'prescription_items.*.medicine_name' => 'required_with:prescription_items|string',
            'prescription_items.*.dosage'        => 'required_with:prescription_items|string',
            'prescription_items.*.frequency'     => 'required_with:prescription_items|string',
            'prescription_items.*.duration'      => 'required_with:prescription_items|string',
        ];
    }
}
