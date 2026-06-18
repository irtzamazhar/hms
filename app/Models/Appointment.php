<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_number', 'patient_id', 'doctor_id', 'department_id',
        'appointment_datetime', 'duration_minutes', 'type', 'status',
        'reason', 'notes', 'fee', 'payment_status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_datetime' => 'datetime',
            'fee' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $prefix = 'APT-' . now()->format('Ymd') . '-';
        $last = static::where('appointment_number', 'like', $prefix . '%')->latest('id')->value('appointment_number');
        $next = $last ? ((int) substr($last, -3) + 1) : 1;

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
