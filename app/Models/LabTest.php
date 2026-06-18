<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTest extends Model
{
    protected $fillable = [
        'category_id', 'name', 'code', 'description', 'cost', 'normal_range',
        'unit', 'sample_type', 'turnaround_hours', 'preparation_instructions', 'status',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LabTestCategory::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(LabBookingItem::class, 'test_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
