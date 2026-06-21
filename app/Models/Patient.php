<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Patient extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'mr_number', 'name', 'cnic', 'phone', 'email', 'gender', 'dob',
        'age', 'blood_group', 'address', 'city',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'allergies', 'medical_history', 'referred_by', 'registered_by', 'status',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
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

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labBookings(): HasMany
    {
        return $this->hasMany(LabBooking::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getAgeDisplayAttribute(): string
    {
        return $this->age ? (string) $this->age : '';
    }

    public static function generateMrNumber(): string
    {
        $last = static::withTrashed()->latest('id')->value('mr_number');
        $next = $last ? ((int) ltrim(substr($last, 3), '0') + 1) : 1;

        return 'MR-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('mr_number', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('cnic', 'like', "%{$search}%");
        });
    }
}
