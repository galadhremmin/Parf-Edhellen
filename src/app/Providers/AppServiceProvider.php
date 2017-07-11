<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\AuditTrailRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        AuditTrailRepository::mapMorps();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ed.php', 'ed'
        );
    }
}
