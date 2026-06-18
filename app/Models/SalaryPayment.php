<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    protected $fillable = [
        'user_id', 'salary_structure_id', 'month', 'year', 'basic_salary',
        'total_allowances', 'total_deductions', 'bonus', 'overtime',
        'net_salary', 'payment_date', 'payment_method', 'transaction_reference',
        'status', 'remarks', 'generated_by', 'paid_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'basic_salary' => 'decimal:2',
            'total_allowances' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'bonus' => 'decimal:2',
            'overtime' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1)) . ' ' . $this->year;
    }
}
