<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class IpdAdmission extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'admission_number', 'patient_id', 'doctor_id', 'department_id',
        'ward_id', 'room_id', 'bed_id', 'admission_datetime', 'discharge_datetime',
        'admission_diagnosis', 'discharge_diagnosis', 'treatment_summary',
        'admission_type', 'status', 'daily_bed_charge', 'doctor_charges',
        'nursing_charges', 'medicine_charges', 'lab_charges', 'other_charges',
        'total_amount', 'discount', 'net_amount', 'paid_amount',
        'payment_status', 'notes', 'admitted_by', 'discharged_by',
    ];

    protected function casts(): array
    {
        return [
            'admission_datetime' => 'datetime',
            'discharge_datetime' => 'datetime',
            'daily_bed_charge' => 'decimal:2',
            'doctor_charges' => 'decimal:2',
            'nursing_charges' => 'decimal:2',
            'medicine_charges' => 'decimal:2',
            'lab_charges' => 'decimal:2',
            'other_charges' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(IpdTreatment::class);
    }

    public function admittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }

    public function dischargedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discharged_by');
    }

    public function getDaysAdmittedAttribute(): int
    {
        $end = $this->discharge_datetime ?? now();

        return (int) $this->admission_datetime->diffInDays($end);
    }

    public static function generateAdmissionNumber(): string
    {
        $prefix = 'IPD-' . now()->format('Y') . '-';
        $last = static::where('admission_number', 'like', $prefix . '%')->latest('id')->value('admission_number');
        $next = $last ? ((int) substr($last, -5) + 1) : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
