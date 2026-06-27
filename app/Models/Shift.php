<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'type', 'start_time', 'end_time', 'status'];

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function closings(): HasMany
    {
        return $this->hasMany(ShiftClosing::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
