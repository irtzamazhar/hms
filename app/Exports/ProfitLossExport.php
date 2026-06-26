<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private array $revenue,
        private Collection $expenseByCategory,
        private float $totalSalaries,
        private float $totalExpenses,
        private float $netProfit,
        private string $from,
        private string $to
    ) {}

    public function array(): array
    {
        $rows = [];

        $rows[] = ['REVENUE', ''];
        $rows[] = ['OPD Revenue', $this->revenue['opd'] ?? 0];
        $rows[] = ['IPD Revenue', $this->revenue['ipd'] ?? 0];
        $rows[] = ['Pharmacy Revenue', $this->revenue['pharmacy'] ?? 0];
        $rows[] = ['Lab Revenue', $this->revenue['lab'] ?? 0];
        $rows[] = ['Other Revenue', $this->revenue['other'] ?? 0];
        $rows[] = ['Total Revenue', array_sum($this->revenue)];

        $rows[] = ['', ''];
        $rows[] = ['EXPENSES', ''];
        foreach ($this->expenseByCategory as $cat) {
            $rows[] = [$cat->name ?? '—', $cat->total ?? 0];
        }
        $rows[] = ['Salaries', $this->totalSalaries];
        $rows[] = ['Total Expenses', $this->totalExpenses];

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
        return 'P&L — '.$this->from.' to '.$this->to;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DCFCE7']]],
        ];
    }
}
