<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Free trial length (days)
    |--------------------------------------------------------------------------
    | New hospitals get this many days free before a subscription is required.
    | Business model: 12 months free, then paid.
    */

    'trial_days' => (int) env('TENANCY_TRIAL_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Default tenant id
    |--------------------------------------------------------------------------
    | The hospital that existing (pre-multi-tenant) data is attributed to during
    | the migration. Phase 2 backfills hospital_id to this value.
    */

    'default_tenant_id' => (int) env('TENANCY_DEFAULT_TENANT_ID', 1),

];
