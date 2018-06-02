<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\{
    Blade,
    Schema
};

use App\Models\Initialization\Morphs;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Reference: https://laravel.com/docs/master/migrations#creating-indexes
        Schema::defaultStringLength(191);
        Morphs::map();

        // @markdown method injection
        Blade::directive('markdown', function (string $data) {
            return "<?php echo (new \App\Helpers\MarkdownParser)->text($data); ?>";
        });
        Blade::directive('markdownInline', function (string $data) {
            return "<?php echo (new \App\Helpers\MarkdownParser)->line($data); ?>";
        });

        Blade::directive('assetpath', function (string $filePath) {
            $root = '/v'.config('ed.version');

            if (empty($filePath)) {
                return $root;
            }

            return $root.($filePath[0] === '/' ? '' : '/').$filePath;
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
