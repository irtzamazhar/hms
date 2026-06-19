<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        private array $revenue,
        private array $expenses,
        private float $netProfit,
        private int $month,
        private int $year
    ) {}

    public function array(): array
    {
        $rows = [];

        $rows[] = ['=== REVENUE ===', ''];
        $rows[] = ['OPD Revenue', $this->revenue['opd'] ?? 0];
        $rows[] = ['Pharmacy Revenue', $this->revenue['pharmacy'] ?? 0];
        $rows[] = ['Lab Revenue', $this->revenue['lab'] ?? 0];
        $rows[] = ['Total Revenue', $this->revenue['total'] ?? 0];

        $rows[] = ['', ''];
        $rows[] = ['=== EXPENSES ===', ''];
        $rows[] = ['Hospital Expenses', $this->expenses['hospital'] ?? 0];
        $rows[] = ['Pharmacy Expenses', $this->expenses['pharmacy'] ?? 0];
        $rows[] = ['Lab Expenses', $this->expenses['lab'] ?? 0];
        $rows[] = ['Salaries', $this->expenses['salaries'] ?? 0];
        $rows[] = ['Total Expenses', $this->expenses['total'] ?? 0];

        $rows[] = ['', ''];
        $rows[] = ['NET PROFIT / (LOSS)', $this->netProfit];

        return $rows;
    }

    public function headings(): array
    {
        return ['Description', 'Amount (₨)'];
    }

    public function title(): string
    {
        return 'P&L — ' . date('F', mktime(0, 0, 0, $this->month, 1)) . ' ' . $this->year;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1  => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DCFCE7']]],
            14 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
