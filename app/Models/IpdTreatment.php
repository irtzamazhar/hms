<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpdTreatment extends Model
{
    protected $fillable = [
        'ipd_admission_id', 'doctor_id', 'treatment_datetime', 'treatment_notes',
        'vital_bp', 'vital_pulse', 'vital_temperature', 'vital_weight', 'vital_spo2',
    ];

    protected function casts(): array
    {
        return [
            'treatment_datetime' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }
}
