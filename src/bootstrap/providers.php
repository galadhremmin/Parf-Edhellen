<?php

return [
        /*
         * Package Service Providers...
         */
        Laravel\Tinker\TinkerServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\SsrServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        /**
         * Custom AWS Service Providers...
         */
        App\Providers\Aws\ComprehendServiceProvider::class,

        /*
         * Custom Service Providers ...
         */
        Watson\Active\ActiveServiceProvider::class, // https://packagist.org/packages/watson/active
        \SocialiteProviders\Manager\ServiceProvider::class, // http://socialiteproviders.github.io/providers/microsoft-live/
        Intervention\Image\ImageServiceProvider::class, // http://image.intervention.io/
];
