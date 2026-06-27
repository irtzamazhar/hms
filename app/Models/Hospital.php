<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A tenant. Each hospital using the SaaS gets one row. Holds the trial /
 * subscription lifecycle that decides whether the tenant may use the system:
 *
 *   - New hospital  -> 12-month free trial (subscription_status = trialing).
 *   - Trial ends     -> must subscribe; otherwise access is gated.
 *   - Subscribed     -> active until subscribed_until.
 *
 * The default trial length lives in config('tenancy.trial_days').
 */
class Hospital extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'status', 'plan',
        'subscription_status', 'trial_ends_at', 'subscribed_until', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'date',
            'subscribed_until' => 'date',
            'settings' => 'array',
        ];
    }

    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function onTrial(): bool
    {
        return $this->subscription_status === 'trialing'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->endOfDay()->isFuture();
    }

    public function trialExpired(): bool
    {
        return $this->trial_ends_at !== null
            && $this->trial_ends_at->endOfDay()->isPast();
    }

    public function subscriptionActive(): bool
    {
        return $this->subscription_status === 'active'
            && ($this->subscribed_until === null || $this->subscribed_until->endOfDay()->isFuture());
    }

    /**
     * The single gate the middleware checks: a suspended tenant is always out;
     * otherwise access requires an active subscription or a live trial.
     */
    public function hasAccess(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        return $this->subscriptionActive() || $this->onTrial();
    }

    public function trialDaysLeft(): int
    {
        if ($this->trial_ends_at === null) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->trial_ends_at->endOfDay(), false));
    }

    /**
     * Provision a brand-new tenant on a free trial.
     */
    public static function startTrial(array $attributes, ?int $trialDays = null): self
    {
        $trialDays ??= (int) config('tenancy.trial_days', 365);

        return static::create(array_merge([
            'status' => 'active',
            'plan' => 'trial',
            'subscription_status' => 'trialing',
            'trial_ends_at' => Carbon::now()->addDays($trialDays)->toDateString(),
        ], $attributes));
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
