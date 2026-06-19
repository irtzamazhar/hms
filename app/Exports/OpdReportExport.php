<?php

namespace App\Exports;

use App\Models\OpdVisit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpdReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        private string $from,
        private string $to,
        private ?string $shift = null,
        private ?int $doctorId = null
    ) {}

    public function query()
    {
        return OpdVisit::with(['patient:id,name,mr_number,phone', 'doctor.user:id,name'])
            ->whereBetween('visit_date', [$this->from, $this->to])
            ->when($this->shift, fn ($q, $s) => $q->where('shift', $s))
            ->when($this->doctorId, fn ($q, $id) => $q->where('doctor_id', $id))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Visit #', 'Date', 'Shift', 'MR #', 'Patient', 'Phone',
            'Doctor', 'Diagnosis', 'Fee (₨)', 'Discount (₨)', 'Net (₨)',
            'Payment', 'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->visit_number,
            $row->visit_date?->format('d/m/Y'),
            ucfirst($row->shift),
            $row->patient?->mr_number,
            $row->patient?->name,
            $row->patient?->phone,
            'Dr. ' . ($row->doctor?->user?->name ?? ''),
            $row->diagnosis,
            $row->consultation_fee,
            $row->discount,
            $row->net_amount,
            ucfirst($row->payment_status),
            ucfirst($row->status),
        ];
    }

    public function title(): string
    {
        return 'OPD Report';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']]],
        ];
    }
}
