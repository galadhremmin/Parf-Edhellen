<?php

namespace App\Http\Middleware;

use \Illuminate\Auth\AuthenticationException;
use Closure;

class CheckRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, string $role)
    {
        if (! $request->user()->memberOf($role)) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }

}
