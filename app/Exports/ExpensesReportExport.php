<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        private string $from,
        private string $to,
        private ?string $module = null
    ) {}

    public function query()
    {
        return Expense::with(['category:id,name', 'createdBy:id,name', 'approvedBy:id,name'])
            ->whereBetween('expense_date', [$this->from, $this->to])
            ->when($this->module, fn ($q, $m) => $q->where('module', $m))
            ->latest('expense_date');
    }

    public function headings(): array
    {
        return [
            'Date', 'Title', 'Category', 'Module', 'Amount (₨)',
            'Payment Method', 'Status', 'Created By', 'Approved By',
        ];
    }

    public function map($row): array
    {
        return [
            $row->expense_date?->format('d/m/Y'),
            $row->title,
            $row->category?->name,
            ucfirst($row->module),
            $row->amount,
            ucfirst(str_replace('_', ' ', $row->payment_method ?? '')),
            ucfirst($row->status),
            $row->createdBy?->name,
            $row->approvedBy?->name ?? '—',
        ];
    }

    public function title(): string
    {
        return 'Expenses';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF3C7']]],
        ];
    }
}
