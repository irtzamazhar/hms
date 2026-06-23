<?php

namespace App\Http\Middleware;

use App\Support\Modules;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    /**
     * Block access to any route belonging to a module that has been
     * disabled site-wide from Settings.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $module = Modules::forRoute($request->route()?->getName());

        if ($module && ! Modules::enabled($module)) {
            abort(403, 'This module is currently disabled.');
        }

        return $next($request);
    }
}
