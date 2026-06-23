<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Token extends Model
{
    protected $fillable = [
        'token_number', 'token_date', 'patient_id', 'doctor_id',
        'department_id', 'shift', 'status', 'priority', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'token_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function nextTokenNumber(string $shift, ?string $date = null): int
    {
        $date = $date ?? today()->toDateString();
        $last = static::where('token_date', $date)->where('shift', $shift)->max('token_number');

        return ($last ?? 0) + 1;
    }
}
