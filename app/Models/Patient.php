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

class Patient extends Model implements Auditable
{
    use AuditableTrait, HasFactory, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'mr_number', 'name', 'cnic', 'phone', 'email', 'gender', 'dob',
        'age', 'blood_group', 'address', 'city',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'allergies', 'medical_history', 'referred_by', 'registered_by', 'status',
    ];

    /**
     * Sensitive (non-searchable) PHI encrypted at rest (CR-1) and excluded
     * from the plaintext audit trail (CR-2).
     */
    public const ENCRYPTED_PHI = [
        'allergies', 'medical_history',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
    ];

    /** Do not write these PHI fields into the audits table in plaintext. */
    protected $auditExclude = [
        'allergies', 'medical_history',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'allergies' => 'encrypted',
            'medical_history' => 'encrypted',
            'emergency_contact_name' => 'encrypted',
            'emergency_contact_phone' => 'encrypted',
            'emergency_contact_relation' => 'encrypted',
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

        return 'MR-'.str_pad($next, 6, '0', STR_PAD_LEFT);
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
