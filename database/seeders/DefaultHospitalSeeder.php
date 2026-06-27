<?php

namespace Database\Seeders;

use App\Models\Hospital;
use Illuminate\Database\Seeder;

/**
 * Ensures the default tenant (id = config('tenancy.default_tenant_id')) exists.
 * Existing single-tenant data is attributed to this hospital when Phase 2
 * backfills hospital_id. Runs first so the id is available to other seeders.
 */
class DefaultHospitalSeeder extends Seeder
{
    public function run(): void
    {
        $id = (int) config('tenancy.default_tenant_id', 1);

        Hospital::query()->updateOrCreate(
            ['id' => $id],
            [
                'name' => 'Default Hospital',
                'slug' => 'default',
                'status' => 'active',
                'plan' => 'trial',
                'subscription_status' => 'trialing',
                'trial_ends_at' => now()->addDays((int) config('tenancy.trial_days', 365))->toDateString(),
            ]
        );
    }
}
