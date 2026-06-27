<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTestCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'code', 'description', 'status'];

    public function tests(): HasMany
    {
        return $this->hasMany(LabTest::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
