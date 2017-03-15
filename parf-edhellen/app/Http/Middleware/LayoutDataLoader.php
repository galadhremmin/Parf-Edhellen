<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use View;

class LayoutDataLoader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        View::composer('_layouts.default', function ($view)  {
          $languages = Language::all()->sortBy('Order');
           $view->with('allLanguages', $languages);
        });

        return $next($request);
    }
}
