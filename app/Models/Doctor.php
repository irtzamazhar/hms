<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'department_id', 'doctor_id', 'qualification',
        'specialization', 'cnic', 'phone', 'consultation_fee',
        'bio', 'available_days', 'available_from', 'available_to', 'status',
    ];

    protected function casts(): array
    {
        return [
            'available_days' => 'array',
            'available_from' => 'datetime:H:i',
            'available_to' => 'datetime:H:i',
            'consultation_fee' => 'decimal:2',
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

    public function opdVisits(): HasMany
    {
        return $this->hasMany(OpdVisit::class);
    }

    public function ipdAdmissions(): HasMany
    {
        return $this->hasMany(IpdAdmission::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }

    public function getFullTitleAttribute(): string
    {
        return 'Dr. ' . $this->user?->name . ' (' . $this->specialization . ')';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
