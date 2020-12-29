<?php

namespace App\Providers\Aws;

use Illuminate\Support\ServiceProvider;

use App\Aws\ComprehendFacade;
use App\Interfaces\{
    IIdentifiesPhrases
};

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
