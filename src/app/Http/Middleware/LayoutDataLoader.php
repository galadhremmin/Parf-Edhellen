<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;

class LayoutDataLoader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        View::composer('_layouts.default', function ($view) use ($request) {
            $user = $request->user();
            $view->with('isAdmin', $user ? $user->isAdministrator() : null);
            $view->with('user', $user);
        });

        return $next($request);
    }
}
