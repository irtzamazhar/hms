<?php

namespace App\Exports;

use App\Models\LabBooking;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LabReportExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private string $from,
        private string $to,
        private ?string $status = null
    ) {}

    public function query()
    {
        return LabBooking::with(['patient:id,name,mr_number', 'doctor.user:id,name'])
            ->withCount('items')
            ->whereBetween('booking_date', [$this->from, $this->to])
            ->when($this->status, fn ($q, $s) => $q->where('status', $s))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Booking #', 'Date', 'MR #', 'Patient',
            'Referred By', 'Tests', 'Gross (₨)', 'Discount (₨)', 'Net (₨)',
            'Payment', 'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->booking_number,
            $row->booking_date?->format('d/m/Y'),
            $row->patient?->mr_number,
            $row->patient?->name,
            $row->doctor ? 'Dr. '.$row->doctor->user?->name : '—',
            $row->items_count,
            $row->total_amount,
            $row->discount,
            $row->net_amount,
            ucfirst($row->payment_status),
            ucfirst($row->status),
        ];
    }

    public function title(): string
    {
        return 'Lab Report';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CFFAFE']]],
        ];
    }
}
