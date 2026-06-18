<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'department_id', 'staff_id', 'designation', 'cnic',
        'phone', 'address', 'emergency_contact', 'basic_salary', 'status',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
