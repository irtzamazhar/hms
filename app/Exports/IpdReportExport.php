<?php

namespace App\Exports;

use App\Models\IpdAdmission;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IpdReportExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private string $from,
        private string $to,
        private ?string $status = null
    ) {}

    public function query()
    {
        return IpdAdmission::with(['patient:id,name,mr_number', 'doctor.user:id,name', 'ward:id,name', 'bed:id,bed_number'])
            ->whereBetween('admission_datetime', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
            ->when($this->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('admission_datetime');
    }

    public function headings(): array
    {
        return [
            'Admission #', 'Admitted', 'Discharged', 'MR #', 'Patient',
            'Doctor', 'Ward', 'Bed', 'Days', 'Status',
            'Bed Charges (₨)', 'Treatment (₨)', 'Other (₨)',
            'Advance (₨)', 'Net Amount (₨)',
        ];
    }

    public function map($row): array
    {
        $days = $row->admission_datetime && $row->discharge_datetime
            ? $row->admission_datetime->diffInDays($row->discharge_datetime)
            : ($row->admission_datetime ? now()->diffInDays($row->admission_datetime) : 0);

        return [
            $row->admission_number,
            $row->admission_datetime?->format('d/m/Y'),
            $row->discharge_datetime?->format('d/m/Y') ?? '—',
            $row->patient?->mr_number,
            $row->patient?->name,
            'Dr. '.($row->doctor?->user?->name ?? ''),
            $row->ward?->name,
            $row->bed?->bed_number,
            $days,
            ucfirst($row->status),
            $row->daily_bed_charge,
            $row->doctor_charges + $row->nursing_charges + $row->medicine_charges + $row->lab_charges,
            $row->other_charges,
            $row->paid_amount,
            $row->net_amount,
        ];
    }

    public function title(): string
    {
        return 'IPD Report';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DCFCE7']]],
        ];
    }
}
