<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the tenant-management console to platform (vendor) admins.
 *
 * A platform admin is a user that belongs to NO hospital (hospital_id is null)
 * and holds the 'manage tenants' permission. This guarantees a hospital's own
 * super-admin (who always has a hospital_id) can never manage other tenants or
 * change their own subscription.
 */
class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hospital_id === null && $user->can('manage tenants'),
            403,
            'Platform administrators only.'
        );

        return $next($request);
    }
}
