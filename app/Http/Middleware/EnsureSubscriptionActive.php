<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates access for tenants whose trial has expired without a paid subscription
 * (or who are suspended). Runs after ResolveTenant. When no tenant is resolved
 * it is a no-op, so unscoped/super-admin contexts and tests are unaffected.
 *
 * The subscription-notice page and logout are always reachable to avoid a
 * redirect loop.
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $hospital = Tenancy::current();

        if ($hospital && ! $hospital->hasAccess()
            && ! $request->routeIs('subscription.*')
            && ! $request->routeIs('logout')) {
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
