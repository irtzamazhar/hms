<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineCategory extends Model
{
    protected $fillable = ['name', 'code', 'description', 'status'];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
