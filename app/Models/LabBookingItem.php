<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LabBookingItem extends Model
{
    protected $fillable = ['booking_id', 'test_id', 'cost', 'discount', 'net_cost', 'status'];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'net_cost' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(LabBooking::class, 'booking_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'test_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(LabReport::class, 'booking_item_id');
    }
}
