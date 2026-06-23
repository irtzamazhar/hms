<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class OpdVisit extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'visit_number', 'patient_id', 'doctor_id', 'department_id',
        'appointment_id', 'token_id', 'visit_date', 'shift',
        'chief_complaints', 'symptoms', 'diagnosis', 'treatment', 'notes',
        'vital_bp', 'vital_pulse', 'vital_temperature', 'vital_weight',
        'vital_height', 'vital_spo2', 'consultation_fee', 'discount',
        'net_amount', 'payment_status', 'payment_method',
        'is_follow_up', 'follow_up_date', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'follow_up_date' => 'date',
            'is_follow_up' => 'boolean',
            'consultation_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'net_amount' => 'decimal:2',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labBookings(): HasMany
    {
        return $this->hasMany(LabBooking::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateVisitNumber(): string
    {
        $prefix = 'OPD-' . now()->format('Ymd') . '-';
        $last = static::where('visit_number', 'like', $prefix . '%')->latest('id')->value('visit_number');
        $next = $last ? ((int) substr($last, -4) + 1) : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
