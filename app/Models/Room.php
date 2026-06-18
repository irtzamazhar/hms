<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['ward_id', 'room_number', 'room_type', 'charge_per_day', 'status'];

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

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
