<?php

namespace App\Http\Middleware;

use App\Models\Hospital;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active tenant for the request and registers it with Tenancy so
 * the BelongsToTenant global scope isolates every query.
 *
 * Resolution order:
 *   1. Subdomain (slug.<base_domain>) — primary, when a base domain is set.
 *   2. The authenticated user's hospital_id — fallback for local/dev and any
 *      non-subdomain host.
 *
 * Runs after `auth`, so the user is available. When neither yields a tenant
 * (e.g. tests, or a super-admin on the bare domain) no tenant is set and the
 * app behaves un-scoped — by design.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $hospital = $this->fromSubdomain($request) ?? $this->fromUser($request);

        if ($hospital) {
            Tenancy::set($hospital);
        }

        return $next($request);
    }

    private function fromSubdomain(Request $request): ?Hospital
    {
        $base = config('tenancy.base_domain');
        if (! $base) {
            return null;
        }

        $host = $request->getHost();
        if ($host === $base || ! str_ends_with($host, '.'.$base)) {
            return null;
        }

        $slug = substr($host, 0, -(strlen($base) + 1));
        if ($slug === '' || $slug === 'www') {
            return null;
        }

        return Hospital::where('slug', $slug)->first();
    }

    private function fromUser(Request $request): ?Hospital
    {
        $user = $request->user();

        return $user?->hospital_id ? Hospital::find($user->hospital_id) : null;
    }
}
