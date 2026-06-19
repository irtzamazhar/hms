<?php

namespace App\Exports;

use App\Models\SalaryPayment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalaryExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        private ?int $month = null,
        private ?int $year = null,
        private ?string $status = null
    ) {}

    public function query()
    {
        return SalaryPayment::with(['user:id,name,employee_id,user_type', 'paidBy:id,name'])
            ->when($this->month, fn ($q, $m) => $q->where('month', $m))
            ->when($this->year, fn ($q, $y) => $q->where('year', $y))
            ->when($this->status, fn ($q, $s) => $q->where('status', $s))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Employee ID', 'Name', 'Type', 'Month', 'Year',
            'Basic (₨)', 'Allowances (₨)', 'Bonus (₨)', 'Overtime (₨)',
            'Deductions (₨)', 'Net Salary (₨)',
            'Status', 'Payment Date', 'Method', 'Reference', 'Paid By',
        ];
    }

    public function map($row): array
    {
        return [
            $row->user?->employee_id,
            $row->user?->name,
            ucfirst($row->user?->user_type ?? ''),
            date('F', mktime(0, 0, 0, $row->month, 1)),
            $row->year,
            $row->basic_salary,
            $row->total_allowances,
            $row->bonus,
            $row->overtime,
            $row->total_deductions,
            $row->net_salary,
            ucfirst($row->status),
            $row->payment_date?->format('d/m/Y') ?? '—',
            ucfirst(str_replace('_', ' ', $row->payment_method ?? '')),
            $row->transaction_reference ?? '—',
            $row->paidBy?->name ?? '—',
        ];
    }

    public function title(): string
    {
        return 'Salary Report';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0FDF4']]],
        ];
    }
}
