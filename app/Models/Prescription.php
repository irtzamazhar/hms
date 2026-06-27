<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Prescription extends Model implements Auditable
{
    use AuditableTrait;
    use BelongsToTenant;

    protected $fillable = ['opd_visit_id', 'patient_id', 'doctor_id', 'prescription_date', 'notes'];

    protected function casts(): array
    {
        return [
            'prescription_date' => 'date',
        ];
    }

    public function opdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
