<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftClosing extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'closing_date', 'shift_id', 'opd_revenue', 'ipd_revenue', 'pharmacy_revenue',
        'lab_revenue', 'other_revenue', 'total_revenue', 'total_expenses',
        'opd_patients', 'ipd_patients', 'lab_tests', 'notes', 'closed_by', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date',
            'closed_at' => 'datetime',
            'opd_revenue' => 'decimal:2',
            'ipd_revenue' => 'decimal:2',
            'pharmacy_revenue' => 'decimal:2',
            'lab_revenue' => 'decimal:2',
            'other_revenue' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'total_expenses' => 'decimal:2',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function getNetProfitAttribute(): float
    {
        return $this->total_revenue - $this->total_expenses;
    }
}
