<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryStructure extends Model
{
    protected $fillable = [
        'user_id', 'basic_salary', 'house_allowance', 'transport_allowance',
        'medical_allowance', 'other_allowances', 'income_tax_deduction',
        'provident_fund_deduction', 'other_deductions', 'effective_from',
        'effective_to', 'is_current',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'house_allowance' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'medical_allowance' => 'decimal:2',
            'other_allowances' => 'decimal:2',
            'income_tax_deduction' => 'decimal:2',
            'provident_fund_deduction' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function getTotalAllowancesAttribute(): float
    {
        return $this->house_allowance + $this->transport_allowance
            + $this->medical_allowance + $this->other_allowances;
    }

    public function getTotalDeductionsAttribute(): float
    {
        return $this->income_tax_deduction + $this->provident_fund_deduction
            + $this->other_deductions;
    }

    public function getGrossSalaryAttribute(): float
    {
        return $this->basic_salary + $this->total_allowances;
    }

    public function getNetSalaryAttribute(): float
    {
        return $this->gross_salary - $this->total_deductions;
    }
}
