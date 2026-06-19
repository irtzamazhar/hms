<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PharmacyReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        private string $from,
        private string $to
    ) {}

    public function query()
    {
        return Sale::with(['patient:id,name,mr_number', 'createdBy:id,name'])
            ->whereBetween('sale_date', [$this->from, $this->to])
            ->where('status', 'completed')
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Sale #', 'Date', 'Patient / Walk-in', 'Items',
            'Subtotal (₨)', 'Discount (₨)', 'Total (₨)',
            'Payment Method', 'Created By',
        ];
    }

    public function map($row): array
    {
        return [
            $row->sale_number,
            $row->sale_date?->format('d/m/Y'),
            $row->patient?->name ?? $row->patient_name ?? 'Walk-in',
            $row->items_count ?? $row->items->count(),
            $row->subtotal,
            $row->discount_amount,
            $row->net_amount,
            ucfirst(str_replace('_', ' ', $row->payment_method ?? '')),
            $row->createdBy?->name,
        ];
    }

    public function title(): string
    {
        return 'Pharmacy Sales';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EDE9FE']]],
        ];
    }
}
