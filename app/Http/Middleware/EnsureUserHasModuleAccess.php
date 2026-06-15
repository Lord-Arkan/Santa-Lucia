<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasModuleAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_if(! $request->user()?->hasModuleAccess($module), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
