<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number', 'patient_id', 'doctor_id', 'opd_visit_id', 'ipd_admission_id',
        'booking_date', 'shift', 'total_amount', 'discount', 'net_amount', 'paid_amount',
        'payment_method', 'payment_status', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
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

    public function opdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class);
    }

    public function ipdAdmission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabBookingItem::class, 'booking_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(LabReport::class, 'booking_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $prefix = 'LAB-' . now()->format('Ymd') . '-';
        $last = static::where('booking_number', 'like', $prefix . '%')->latest('id')->value('booking_number');
        $next = $last ? ((int) substr($last, -4) + 1) : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
