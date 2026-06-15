<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAnyModuleAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $user = $request->user();
        $hasAccess = $user && collect($modules)->contains(fn (string $module) => $user->hasModuleAccess($module));

        abort_unless($hasAccess, Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
