<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchasesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        private ?string $from = null,
        private ?string $to = null,
        private ?int $supplierId = null
    ) {}

    public function query()
    {
        return Purchase::with(['supplier:id,name,company', 'createdBy:id,name'])
            ->when($this->from, fn ($q, $d) => $q->where('purchase_date', '>=', $d))
            ->when($this->to, fn ($q, $d) => $q->where('purchase_date', '<=', $d))
            ->when($this->supplierId, fn ($q, $id) => $q->where('supplier_id', $id))
            ->latest('purchase_date');
    }

    public function headings(): array
    {
        return [
            'PO #', 'Date', 'Supplier', 'Company', 'Invoice #',
            'Items', 'Subtotal (₨)', 'Discount (₨)', 'Tax (₨)', 'Total (₨)',
            'Paid (₨)', 'Due (₨)', 'Payment Status', 'Created By',
        ];
    }

    public function map($row): array
    {
        return [
            $row->purchase_number,
            $row->purchase_date?->format('d/m/Y'),
            $row->supplier?->name,
            $row->supplier?->company ?? '—',
            $row->invoice_number ?? '—',
            $row->items->count(),
            $row->subtotal,
            $row->discount,
            $row->tax,
            $row->total_amount,
            $row->paid_amount,
            $row->due_amount,
            ucfirst($row->payment_status),
            $row->createdBy?->name,
        ];
    }

    public function title(): string
    {
        return 'Purchases';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF9C3']]],
        ];
    }
}
