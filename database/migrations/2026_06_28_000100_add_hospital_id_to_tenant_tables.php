<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant-owned tables that gain a hospital_id for row-level isolation.
     * Deliberately excludes global tables (roles/permissions, modules, sessions,
     * jobs, cache, audits, hospital_settings, hospitals, licenses).
     */
    private array $tables = [
        'users',
        'patients', 'opd_visits', 'prescriptions', 'prescription_items',
        'ipd_admissions', 'ipd_treatments',
        'appointments', 'tokens',
        'lab_bookings', 'lab_booking_items', 'lab_reports', 'lab_tests', 'lab_test_categories',
        'sales', 'sale_items', 'purchases', 'purchase_items',
        'medicines', 'medicine_batches', 'medicine_stocks', 'medicine_categories', 'suppliers',
        'expenses', 'expense_categories',
        'salary_payments', 'salary_structures',
        'doctors', 'staff', 'departments',
        'wards', 'rooms', 'beds',
        'shifts', 'shift_assignments', 'shift_closings',
        'daily_closing_reports', 'monthly_closing_reports',
    ];

    public function up(): void
    {
        $defaultId = (int) config('tenancy.default_tenant_id', 1);

        // Ensure the default tenant exists so backfilled rows reference a real row.
        if (Schema::hasTable('hospitals') && DB::table('hospitals')->where('id', $defaultId)->doesntExist()) {
            DB::table('hospitals')->insert([
                'id' => $defaultId,
                'name' => 'Default Hospital',
                'slug' => 'default',
                'status' => 'active',
                'subscription_status' => 'trialing',
                'trial_ends_at' => now()->addDays((int) config('tenancy.trial_days', 365))->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name) || Schema::hasColumn($name, 'hospital_id')) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) {
                // Nullable + indexed (no FK constraint yet — added in a later
                // hardening step to avoid migration-order/engine issues).
                $table->unsignedBigInteger('hospital_id')->nullable()->index();
            });

            // Attribute all existing (pre-multi-tenant) rows to the default tenant.
            DB::table($name)->whereNull('hospital_id')->update(['hospital_id' => $defaultId]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            if (Schema::hasTable($name) && Schema::hasColumn($name, 'hospital_id')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropColumn('hospital_id');
                });
            }
        }
    }
};
