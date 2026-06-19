<?php

namespace App\Exports;

use App\Models\Patient;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PatientsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
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
        return [
            $row->mr_number,
            $row->name,
            ucfirst($row->gender ?? ''),
            $row->date_of_birth?->format('d/m/Y'),
            $row->date_of_birth ? $row->date_of_birth->age . ' yrs' : '—',
            $row->blood_group ?? '—',
            $row->phone,
            $row->email ?? '—',
            $row->cnic ?? '—',
            $row->address ?? '—',
            $row->city ?? '—',
            $row->emergency_contact_name ?? '—',
            $row->emergency_contact_phone ?? '—',
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
