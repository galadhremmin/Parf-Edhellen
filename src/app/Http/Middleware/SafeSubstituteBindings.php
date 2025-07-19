<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\SubstituteBindings;

class SafeSubstituteBindings extends SubstituteBindings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Only run SubstituteBindings if the route is available
        if ($request->route() !== null) {
            return parent::handle($request, $next);
        }

        // If no route is available, just continue without binding substitution
        return $next($request);
    }
} 