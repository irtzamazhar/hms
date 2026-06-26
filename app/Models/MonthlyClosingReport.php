<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyClosingReport extends Model
{
    protected $fillable = [
        'month', 'year', 'total_opd_patients', 'total_ipd_admissions',
        'opd_revenue', 'ipd_revenue', 'pharmacy_revenue', 'lab_revenue',
        'total_revenue', 'total_expenses', 'total_salaries',
        'pharmacy_purchase_cost', 'pharmacy_profit', 'lab_profit',
        'net_profit', 'notes', 'closed_by', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
            'net_profit' => 'decimal:2',
            'total_revenue' => 'decimal:2',
        ];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1)).' '.$this->year;
    }
}
