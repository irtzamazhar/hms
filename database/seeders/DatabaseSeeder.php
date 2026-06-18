<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            HospitalSettingSeeder::class,
            ShiftSeeder::class,
            ExpenseCategorySeeder::class,
            DepartmentSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
