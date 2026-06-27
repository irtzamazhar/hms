<?php

namespace App\Support;

use App\Models\Hospital;

/**
 * Holds the current tenant for the lifetime of a request (or console command).
 *
 * Phase 1 provides the context only; it is populated by the tenant-resolution
 * middleware (Phase 3) and consumed by the BelongsToTenant global scope (Phase 2)
 * so every query is automatically isolated to the active hospital.
 */
class Tenancy
{
    private static ?Hospital $current = null;

    public static function set(?Hospital $hospital): void
    {
        self::$current = $hospital;
    }

    public static function current(): ?Hospital
    {
        return self::$current;
    }

    public static function id(): ?int
    {
        return self::$current?->id;
    }

    public static function check(): bool
    {
        return self::$current !== null;
    }

    public static function forget(): void
    {
        self::$current = null;
    }

    /**
     * Run a callback as a specific tenant, restoring the previous context after.
     * Useful for jobs/console work that operates on one hospital at a time.
     */
    public static function runFor(Hospital $hospital, callable $callback): mixed
    {
        $previous = self::$current;
        self::$current = $hospital;

        try {
            return $callback();
        } finally {
            self::$current = $previous;
        }
    }
}
