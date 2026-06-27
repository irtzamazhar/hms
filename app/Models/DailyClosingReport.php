<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosingReport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'report_date', 'total_opd_patients', 'total_ipd_admissions', 'total_ipd_discharged',
        'opd_revenue', 'ipd_revenue', 'pharmacy_revenue', 'lab_revenue', 'other_revenue',
        'total_revenue', 'hospital_expenses', 'pharmacy_expenses', 'lab_expenses',
        'salary_expenses', 'total_expenses', 'net_profit', 'notes', 'closed_by', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'closed_at' => 'datetime',
            'opd_revenue' => 'decimal:2',
            'pharmacy_revenue' => 'decimal:2',
            'lab_revenue' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'net_profit' => 'decimal:2',
        ];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
