<?php

namespace App\Models\Concerns;

use App\Models\Hospital;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Row-level multi-tenancy for an Eloquent model.
 *
 * When a tenant is active (App\Support\Tenancy), this trait:
 *   1. auto-fills `hospital_id` on create, and
 *   2. adds a global scope so every query is filtered to the current tenant.
 *
 * When no tenant is active (console, super-admin cross-tenant tooling, tests
 * that don't set one) it is a no-op, so nothing is silently hidden.
 *
 * NOTE (Phase 2): apply this trait to a model only after its table has a
 * `hospital_id` column. It is intentionally not yet attached to any model.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new class implements Scope
        {
            public function apply(Builder $builder, Model $model): void
            {
                if (Tenancy::check()) {
                    $builder->where($model->getTable().'.hospital_id', Tenancy::id());
                }
            }
        });

        static::creating(function (Model $model): void {
            if (Tenancy::check() && empty($model->getAttribute('hospital_id'))) {
                $model->setAttribute('hospital_id', Tenancy::id());
            }
        });
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
