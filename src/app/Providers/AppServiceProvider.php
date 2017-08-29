<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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

        // @markdown method injection
        Blade::directive('markdown', function (string $data) {
            return "<?php \$md = new \App\Helpers\MarkdownParser(['>', '#']); echo \$md->parse($data); ?>";
        });
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
