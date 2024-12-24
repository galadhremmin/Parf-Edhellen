<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next, string $role)
    {
        $user = $request->user();

        if (! $user->isRoot() && ! $user->memberOf($role)) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
