<?php

namespace App\Providers\Aws;

use App\Aws\ComprehendFacade;
use App\Interfaces\IIdentifiesPhrases;
use Illuminate\Support\ServiceProvider;

class ComprehendServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(IIdentifiesPhrases::class, ComprehendFacade::class);
    }
}
