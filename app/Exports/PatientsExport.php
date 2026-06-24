<?php

namespace App\Exports;

use App\Models\Patient;
use App\Support\Csv;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PatientsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private ?string $search = null,
        private ?string $gender = null,
        private ?string $bloodGroup = null
    ) {}

    public function query()
    {
        return Patient::query()
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('mr_number', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")))
            ->when($this->gender, fn ($q, $g) => $q->where('gender', $g))
            ->when($this->bloodGroup, fn ($q, $b) => $q->where('blood_group', $b))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'MR #', 'Name', 'Gender', 'DOB', 'Age', 'Blood Group',
            'Phone', 'Email', 'CNIC', 'Address', 'City',
            'Emergency Contact', 'Emergency Phone', 'Registered',
        ];
    }

    public function map($row): array
    {
        // EXC-1: pass user-controlled text through Csv::safe() to neutralise
        // spreadsheet formula injection (=, +, -, @).
        return [
            Csv::safe($row->mr_number),
            Csv::safe($row->name),
            ucfirst($row->gender ?? ''),
            $row->dob?->format('d/m/Y'),
            $row->dob ? $row->dob->age.' yrs' : '—',
            $row->blood_group ?? '—',
            Csv::safe($row->phone),
            Csv::safe($row->email ?? '—'),
            Csv::safe($row->cnic ?? '—'),
            Csv::safe($row->address ?? '—'),
            Csv::safe($row->city ?? '—'),
            Csv::safe($row->emergency_contact_name ?? '—'),
            Csv::safe($row->emergency_contact_phone ?? '—'),
            $row->created_at?->format('d/m/Y'),
        ];
    }

    public function title(): string
    {
        return 'Patients';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E0F2FE']]],
        ];
    }
}
