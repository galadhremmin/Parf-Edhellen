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
        View::composer('_layouts.default', function ($view) use ($request)  {
            $entities = Language::all()->sortBy('order')->toArray();
            $langages = [];
            foreach ($entities as $entity) {
                $languages[] = $entity;
            }

            $view->with('allLanguages', json_encode($languages));

            $user = $request->user();
            $view->with('admin', $user && $user->isAdministrator());
        });

        return $next($request);
    }
}
