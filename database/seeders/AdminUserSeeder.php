<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hms.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'user_type' => 'super_admin',
                'employee_id' => 'EMP-0001',
                'status' => 'active',
                'joining_date' => now(),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');

        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@hms.com'],
            [
                'name' => 'Front Desk',
                'password' => Hash::make('Admin@123'),
                'user_type' => 'receptionist',
                'employee_id' => 'EMP-0002',
                'status' => 'active',
                'joining_date' => now(),
                'email_verified_at' => now(),
            ]
        );
        $receptionist->assignRole('receptionist');
    }
}
