<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'General Medicine',   'code' => 'GM'],
            ['name' => 'Emergency',          'code' => 'ER'],
            ['name' => 'Surgery',            'code' => 'SURG'],
            ['name' => 'Gynecology & OBS',   'code' => 'GYNE'],
            ['name' => 'Pediatrics',         'code' => 'PEDS'],
            ['name' => 'Cardiology',         'code' => 'CARD'],
            ['name' => 'Orthopedics',        'code' => 'ORTH'],
            ['name' => 'ENT',                'code' => 'ENT'],
            ['name' => 'Ophthalmology',      'code' => 'OPTH'],
            ['name' => 'Dermatology',        'code' => 'DERM'],
            ['name' => 'Neurology',          'code' => 'NEURO'],
            ['name' => 'Radiology',          'code' => 'XRAY'],
            ['name' => 'Pathology / Lab',    'code' => 'LAB'],
            ['name' => 'Pharmacy',           'code' => 'PHRM'],
            ['name' => 'ICU',                'code' => 'ICU'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['code' => $department['code']], $department);
        }
    }
}
