<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bed extends Model
{
    protected $fillable = ['ward_id', 'room_id', 'bed_number', 'bed_type', 'charge_per_day', 'status'];

    protected function casts(): array
    {
        return [
            'charge_per_day' => 'decimal:2',
        ];
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function currentAdmission()
    {
        return $this->hasOne(IpdAdmission::class)->where('status', 'admitted');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
