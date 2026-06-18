<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Morning Shift', 'type' => 'morning', 'start_time' => '08:00:00', 'end_time' => '14:00:00'],
            ['name' => 'Evening Shift', 'type' => 'evening', 'start_time' => '14:00:00', 'end_time' => '20:00:00'],
            ['name' => 'Night Shift',   'type' => 'night',   'start_time' => '20:00:00', 'end_time' => '08:00:00'],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['type' => $shift['type']], $shift);
        }
    }
}
