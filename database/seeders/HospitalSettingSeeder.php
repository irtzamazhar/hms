<?php

namespace Database\Seeders;

use App\Models\HospitalSetting;
use Illuminate\Database\Seeder;

class HospitalSettingSeeder extends Seeder
{
    public function run(): void
    {
        HospitalSetting::firstOrCreate([], [
            'hospital_name' => 'City General Hospital',
            'email' => 'info@cityhospital.com',
            'phone' => '+92-300-0000000',
            'address' => '123 Main Street',
            'city' => 'Karachi',
            'state' => 'Sindh',
            'country' => 'Pakistan',
            'currency' => 'PKR',
            'currency_symbol' => '₨',
            'timezone' => 'Asia/Karachi',
            'low_stock_alert' => true,
            'low_stock_threshold' => 10,
        ]);
    }
}
