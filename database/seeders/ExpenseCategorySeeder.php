<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Utilities',      'code' => 'UTIL',  'module' => 'hospital'],
            ['name' => 'Electricity',    'code' => 'ELEC',  'module' => 'hospital'],
            ['name' => 'Internet',       'code' => 'NET',   'module' => 'hospital'],
            ['name' => 'Maintenance',    'code' => 'MAINT', 'module' => 'hospital'],
            ['name' => 'Cleaning',       'code' => 'CLN',   'module' => 'hospital'],
            ['name' => 'Medical Supply', 'code' => 'MSUP',  'module' => 'hospital'],
            ['name' => 'Pharmacy Cost',  'code' => 'PHRM',  'module' => 'pharmacy'],
            ['name' => 'Lab Equipment',  'code' => 'LABEQ', 'module' => 'laboratory'],
            ['name' => 'Lab Reagents',   'code' => 'LABR',  'module' => 'laboratory'],
            ['name' => 'Miscellaneous',  'code' => 'MISC',  'module' => 'general'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(['code' => $category['code']], $category);
        }
    }
}
