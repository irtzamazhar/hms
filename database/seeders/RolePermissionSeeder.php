<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'view dashboard',

            // Patients
            'view patients', 'create patients', 'edit patients', 'delete patients',

            // OPD
            'view opd', 'create opd', 'edit opd', 'delete opd',

            // IPD
            'view ipd', 'create ipd', 'edit ipd', 'delete ipd', 'discharge patients',

            // Appointments
            'view appointments', 'create appointments', 'edit appointments', 'delete appointments',

            // Tokens
            'view tokens', 'create tokens', 'manage tokens',

            // Doctors
            'view doctors', 'create doctors', 'edit doctors', 'delete doctors',

            // Staff
            'view staff', 'create staff', 'edit staff', 'delete staff',

            // Departments
            'view departments', 'create departments', 'edit departments', 'delete departments',

            // Wards & Beds
            'view wards', 'create wards', 'edit wards', 'delete wards',

            // Pharmacy
            'view pharmacy', 'manage medicines', 'create sales', 'view sales',
            'manage purchases', 'view purchases', 'view pharmacy reports',

            // Laboratory
            'view laboratory', 'create lab bookings', 'enter lab results',
            'verify lab reports', 'view lab reports',

            // Expenses
            'view expenses', 'create expenses', 'edit expenses', 'delete expenses', 'approve expenses',

            // Salary
            'view salaries', 'manage salaries', 'pay salaries',

            // Shifts
            'view shifts', 'manage shifts', 'close shifts',

            // Reports
            'view reports', 'export reports', 'close daily reports', 'close monthly reports',

            // Settings
            'view settings', 'manage settings',

            // Users
            'view users', 'create users', 'edit users', 'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'super_admin' => Permission::all()->pluck('name')->toArray(),

            'hospital_admin' => [
                'view dashboard', 'view patients', 'create patients', 'edit patients',
                'view opd', 'create opd', 'edit opd',
                'view ipd', 'create ipd', 'edit ipd', 'discharge patients',
                'view appointments', 'create appointments', 'edit appointments',
                'view tokens', 'create tokens', 'manage tokens',
                'view doctors', 'view staff', 'view departments', 'view wards',
                'view pharmacy', 'view pharmacy reports',
                'view laboratory', 'view lab reports',
                'view expenses', 'create expenses', 'approve expenses',
                'view salaries', 'manage salaries', 'pay salaries',
                'view shifts', 'manage shifts', 'close shifts',
                'view reports', 'export reports', 'close daily reports', 'close monthly reports',
                'view settings', 'view users',
            ],

            'receptionist' => [
                'view dashboard', 'view patients', 'create patients', 'edit patients',
                'view opd', 'create opd', 'edit opd',
                'view appointments', 'create appointments', 'edit appointments',
                'view tokens', 'create tokens', 'manage tokens',
                'view ipd', 'create ipd',
            ],

            'doctor' => [
                'view dashboard', 'view patients', 'view opd', 'edit opd',
                'view ipd', 'edit ipd', 'discharge patients',
                'view appointments', 'view lab reports', 'create lab bookings',
            ],

            'nurse' => [
                'view dashboard', 'view patients', 'view opd', 'view ipd',
                'view appointments', 'view wards',
            ],

            'pharmacist' => [
                'view dashboard', 'view pharmacy', 'manage medicines',
                'create sales', 'view sales', 'manage purchases', 'view purchases',
                'view pharmacy reports',
            ],

            'lab_technician' => [
                'view dashboard', 'view laboratory', 'create lab bookings',
                'enter lab results', 'view lab reports',
            ],

            'accountant' => [
                'view dashboard', 'view expenses', 'create expenses', 'edit expenses',
                'view salaries', 'manage salaries', 'pay salaries',
                'view reports', 'export reports', 'close daily reports', 'close monthly reports',
                'view pharmacy reports', 'view lab reports',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
