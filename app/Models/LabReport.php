<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabReport extends Model
{
    protected $fillable = [
        'booking_id', 'booking_item_id', 'test_id', 'patient_id', 'sample_id',
        'sample_collected_at', 'result_entered_at', 'result_value', 'result_unit',
        'normal_range', 'result_flag', 'result_notes', 'report_file',
        'technician_id', 'verified_by', 'verified_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'sample_collected_at' => 'datetime',
            'result_entered_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(LabBooking::class, 'booking_id');
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(LabBookingItem::class, 'booking_item_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
