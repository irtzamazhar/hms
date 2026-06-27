<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Doctor extends Model implements Auditable
{
    use AuditableTrait, HasFactory, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'user_id', 'department_id', 'doctor_id', 'qualification',
        'specialization', 'cnic', 'phone', 'consultation_fee',
        'bio', 'available_days', 'available_from', 'available_to', 'appointment_duration', 'status',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
        ];
    }

    // available_days may be double-encoded JSON (legacy seeder data) or a plain array — normalise to lowercase array
    public function getAvailableDaysAttribute(mixed $value): array
    {
        if (is_array($value)) {
            return array_map('strtolower', $value);
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            // Handle double-encoded: first decode gives a string, decode again
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            if (is_array($decoded)) {
                return array_map('strtolower', $decoded);
            }
        }

        return [];
    }

    public function setAvailableDaysAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }
        $this->attributes['available_days'] = json_encode(
            is_array($value) ? array_map('strtolower', array_values($value)) : []
        );
    }

    public function user(): BelongsTo
    {
        // Include soft-deleted users so a deactivated account never breaks
        // listings/details that read $doctor->user->name.
        return $this->belongsTo(User::class)->withTrashed();
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
        return 'Dr. '.$this->user?->name.' ('.$this->specialization.')';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
