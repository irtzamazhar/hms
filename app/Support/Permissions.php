<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

/**
 * Single source of truth for the application's permission catalogue.
 *
 * Both the seeder (RolePermissionSeeder) and the Access Control UI
 * (roles/permissions management) consume this so the grouped matrix
 * and the seeded permissions never drift apart.
 */
class Permissions
{
    /**
     * Permissions grouped by module label. The label is only used for display.
     *
     * @return array<string, list<string>>
     */
    public static function groups(): array
    {
        return [
            'Dashboard' => ['view dashboard'],

            'Patients' => ['view patients', 'create patients', 'edit patients', 'delete patients'],

            'OPD' => ['view opd', 'create opd', 'edit opd', 'delete opd'],

            'IPD' => ['view ipd', 'create ipd', 'edit ipd', 'delete ipd', 'discharge patients'],

            'Appointments' => ['view appointments', 'create appointments', 'edit appointments', 'delete appointments'],

            'Tokens' => ['view tokens', 'create tokens', 'manage tokens'],

            'Doctors' => ['view doctors', 'create doctors', 'edit doctors', 'delete doctors'],

            'Staff' => ['view staff', 'create staff', 'edit staff', 'delete staff'],

            'Departments' => ['view departments', 'create departments', 'edit departments', 'delete departments'],

            'Wards & Beds' => ['view wards', 'create wards', 'edit wards', 'delete wards'],

            'Pharmacy' => [
                'view pharmacy', 'manage medicines', 'create sales', 'view sales',
                'manage purchases', 'view purchases', 'view pharmacy reports',
            ],

            'Laboratory' => [
                'view laboratory', 'create lab bookings', 'enter lab results',
                'verify lab reports', 'view lab reports', 'manage lab tests',
            ],

            'Expenses' => ['view expenses', 'create expenses', 'edit expenses', 'delete expenses', 'approve expenses'],

            'Salary' => ['view salaries', 'manage salaries', 'pay salaries'],

            'Shifts' => ['view shifts', 'manage shifts', 'close shifts'],

            'Reports' => ['view reports', 'export reports', 'close daily reports', 'close monthly reports'],

            'Settings' => ['view settings', 'manage settings'],

            'Users' => ['view users', 'create users', 'edit users', 'delete users'],

            'Access Control' => [
                'view roles', 'create roles', 'edit roles', 'delete roles',
                'view permissions', 'create permissions', 'delete permissions',
                'assign user permissions',
            ],
        ];
    }

    /**
     * Flat list of every permission name defined in the catalogue.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_merge(...array_values(static::groups()));
    }

    /**
     * Resolve the group label for a given permission name.
     * Returns "Other" for permissions created at runtime that are not catalogued.
     */
    public static function groupFor(string $permission): string
    {
        foreach (static::groups() as $label => $names) {
            if (in_array($permission, $names, true)) {
                return $label;
            }
        }

        return 'Other';
    }

    /**
     * Module labels offered when creating a permission (catalogue groups + "Other").
     *
     * @return list<string>
     */
    public static function moduleOptions(): array
    {
        return [...array_keys(static::groups()), 'Other'];
    }

    /**
     * Group a collection of Permission models for display.
     *
     * A permission's module is its stored `group` column when set, otherwise the
     * catalogue group derived from its name. Catalogue groups keep their defined
     * order; any custom groups (and "Other") are appended in encounter order.
     *
     * @param  Collection<int, Permission>  $permissions
     * @return array<string, Collection>
     */
    public static function grouped(Collection $permissions): array
    {
        $grouped = [];
        foreach (array_keys(static::groups()) as $label) {
            $grouped[$label] = collect();
        }

        foreach ($permissions as $permission) {
            $label = $permission->group ?: static::groupFor($permission->name);
            $grouped[$label] ??= collect();
            $grouped[$label]->push($permission);
        }

        return array_filter($grouped, fn ($items) => $items->isNotEmpty());
    }
}
