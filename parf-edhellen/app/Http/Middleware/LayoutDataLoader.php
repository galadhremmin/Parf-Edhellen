<?php

namespace App\Http\Middleware;

use App\Language;
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

        View::composer('layouts.default', function ($view)  {
          $languages = Language::all()->sortBy('Name');
           $view->with('allLanguages', $languages);
        });

        return $next($request);
    }
}
