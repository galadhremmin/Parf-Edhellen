<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IpGate
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ips = config('ed.blocked_ips_due_to_violations');

        if (in_array($request->ip(), $ips) && ! $request->routeIs('blocked')) {
            return $request->ajax()
                ? response('', 403)
                : redirect(route('blocked'));
        } elseif ($request->routeIs('blocked') && ! $request->ajax()) {
            return redirect('/');
        }

        return $next($request);
    }
}
