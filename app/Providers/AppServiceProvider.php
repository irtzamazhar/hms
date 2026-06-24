<?php

namespace App\Providers;

use App\Models\HospitalSetting;
use App\Support\Modules;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Cache key holding the resolved hospital timezone. */
    public const TIMEZONE_CACHE_KEY = 'hospital.timezone';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applyHospitalTimezone();
        $this->registerModuleDirectives();
        $this->registerSecurityLogging();
    }

    /**
     * LOG-1: record failed logins and lockouts to the application log so
     * brute-force / credential-stuffing attempts are auditable.
     */
    private function registerSecurityLogging(): void
    {
        Event::listen(Failed::class, function (Failed $event) {
            Log::channel(config('logging.default'))->warning('auth.failed', [
                'email' => $event->credentials['email'] ?? null,
                'guard' => $event->guard,
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]);
        });

        Event::listen(Lockout::class, function () {
            Log::channel(config('logging.default'))->warning('auth.lockout', [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]);
        });
    }

    /**
     * Blade helpers for gating UI by enabled modules:
     *
     *   @module('opd') ... @endmodule
     *
     *   @moduleany(['opd','ipd']) ... @endmoduleany
     */
    private function registerModuleDirectives(): void
    {
        Blade::if('module', fn (string $key) => Modules::enabled($key));
        Blade::if('moduleany', fn (array $keys) => Modules::anyEnabled($keys));
    }

    /**
     * The hospital's operational timezone is configured in Settings but Laravel
     * defaults to UTC. Without applying it, now()/today() drift from how records
     * are entered and displayed, so "today" filters (e.g. the dashboard) miss
     * same-day data. Apply the configured timezone for the whole request.
     */
    private function applyHospitalTimezone(): void
    {
        try {
            if (! Schema::hasTable('hospital_settings')) {
                return;
            }

            $timezone = Cache::remember(
                self::TIMEZONE_CACHE_KEY,
                now()->addHours(12),
                fn () => HospitalSetting::query()->value('timezone')
            );
        } catch (\Throwable) {
            // DB unavailable (e.g. during early console commands) — keep the default.
            return;
        }

        if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }
    }
}
