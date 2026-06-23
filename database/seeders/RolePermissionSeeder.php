<?php

namespace Database\Seeders;

use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permission catalogue lives in App\Support\Permissions so the
        // Access Control UI and this seeder share one source of truth.
        foreach (Permissions::all() as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission],
                ['group' => Permissions::groupFor($permission)]
            );
        }

        $roles = [
            'super_admin' => Permission::all()->pluck('name')->toArray(),

            'hospital_admin' => [
                'view dashboard', 'view patients', 'create patients', 'edit patients',
                'view opd', 'create opd', 'edit opd',
                'view ipd', 'create ipd', 'edit ipd', 'discharge patients',
                'view appointments', 'create appointments', 'edit appointments',
                'view tokens', 'create tokens', 'manage tokens',
                'view doctors', 'create doctors', 'edit doctors',
                'view staff', 'create staff', 'edit staff',
                'view departments', 'create departments', 'edit departments', 'delete departments',
                'view wards', 'create wards', 'edit wards', 'delete wards',
                'view pharmacy', 'view pharmacy reports',
                'view laboratory', 'view lab reports', 'manage lab tests',
                'view expenses', 'create expenses', 'approve expenses',
                'view salaries', 'manage salaries', 'pay salaries',
                'view shifts', 'manage shifts', 'close shifts',
                'view reports', 'export reports', 'close daily reports', 'close monthly reports',
                'view settings', 'view users', 'create users', 'edit users',
                'view roles', 'view permissions',
            ],

            'receptionist' => [
                'view patients', 'create patients', 'edit patients',
                'view opd', 'create opd', 'edit opd',
                'view appointments', 'create appointments', 'edit appointments',
                'view tokens', 'create tokens', 'manage tokens',
                'view ipd', 'create ipd',
            ],

            'doctor' => [
                'view patients', 'view opd', 'edit opd',
                'view ipd', 'edit ipd', 'discharge patients',
                'view appointments', 'view lab reports', 'create lab bookings',
            ],

            'nurse' => [
                'view patients', 'view opd', 'view ipd',
                'view appointments', 'view wards',
            ],

            'pharmacist' => [
                'view pharmacy', 'manage medicines',
                'create sales', 'view sales', 'manage purchases', 'view purchases',
                'view pharmacy reports',
            ],

            'lab_technician' => [
                'view laboratory', 'create lab bookings',
                'enter lab results', 'view lab reports',
            ],

            'accountant' => [
                'view expenses', 'create expenses', 'edit expenses',
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
