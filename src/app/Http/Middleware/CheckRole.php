<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @param  string|null  $route
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next, string $role, ?string $route = null)
    {
        $user = $request->user();

        if (! $user->isRoot() && ! $user->memberOf($role)) {
            if (! empty($route) && ! $request->expectsJson()) {
                return redirect()->route($route);
            }

            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
