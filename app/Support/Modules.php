<?php

namespace App\Support;

use App\Models\Module;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for the site's toggleable feature modules.
 *
 * A super admin can enable/disable each module from Settings; the state is
 * enforced both in the sidebar (hidden links) and on every web route
 * (EnsureModuleEnabled middleware). Core areas — Dashboard, Settings and
 * Access Control (Users/Roles/Permissions) — are intentionally NOT toggleable
 * so an admin can never lock themselves out.
 */
class Modules
{
    public const CACHE_KEY = 'site.modules.states';

    /**
     * The toggleable modules, in display order.
     *
     * Each module maps to the route-name prefixes it owns; the middleware uses
     * these to decide whether an incoming request belongs to a disabled module.
     *
     * @return array<string, array{label: string, routes: list<string>}>
     */
    public static function catalogue(): array
    {
        return [
            'patients' => ['label' => 'Patients', 'routes' => ['patients']],
            'tokens' => ['label' => 'Tokens', 'routes' => ['tokens']],
            'appointments' => ['label' => 'Appointments', 'routes' => ['appointments']],
            'opd' => ['label' => 'OPD', 'routes' => ['opd']],
            'ipd' => ['label' => 'IPD', 'routes' => ['ipd']],
            'wards' => ['label' => 'Wards & Beds', 'routes' => ['wards']],
            'pharmacy' => ['label' => 'Pharmacy', 'routes' => ['pharmacy', 'medicines', 'purchases', 'suppliers']],
            'laboratory' => ['label' => 'Laboratory', 'routes' => ['lab']],
            'expenses' => ['label' => 'Expenses', 'routes' => ['expenses']],
            'salaries' => ['label' => 'Salaries', 'routes' => ['salaries']],
            'reports' => ['label' => 'Reports', 'routes' => ['reports']],
            'doctors' => ['label' => 'Doctors', 'routes' => ['doctors']],
            'staff' => ['label' => 'Staff', 'routes' => ['staff']],
            'departments' => ['label' => 'Departments', 'routes' => ['departments']],
            'shifts' => ['label' => 'Shifts', 'routes' => ['shifts']],
        ];
    }

    /**
     * Map of module key => enabled (bool), read from the DB and cached.
     * Modules with no row default to enabled.
     *
     * @return array<string, bool>
     */
    public static function states(): array
    {
        try {
            if (! Schema::hasTable('modules')) {
                return [];
            }

            return Cache::remember(
                self::CACHE_KEY,
                now()->addHours(12),
                fn () => Module::pluck('enabled', 'key')->map(fn ($v) => (bool) $v)->all()
            );
        } catch (\Throwable) {
            return [];
        }
    }

    public static function enabled(string $key): bool
    {
        // Unknown / unsaved modules default to enabled.
        return self::states()[$key] ?? true;
    }

    /**
     * True when at least one of the given module keys is enabled.
     * Used to hide sidebar group labels whose modules are all disabled.
     *
     * @param  list<string>  $keys
     */
    public static function anyEnabled(array $keys): bool
    {
        foreach ($keys as $key) {
            if (self::enabled($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve which module (if any) owns a given route name.
     */
    public static function forRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach (self::catalogue() as $key => $meta) {
            foreach ($meta['routes'] as $prefix) {
                if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                    return $key;
                }
            }
        }

        return null;
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
