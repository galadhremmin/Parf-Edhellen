<?php

namespace App\Providers;

use App\Models\Initialization\Morphs;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        // https://laravel.com/docs/8.x/upgrade#pagination-defaults
        Paginator::useBootstrap();

        // @markdown method injection
        Blade::directive('markdown', function (string $expression) {
            return "<?php echo(resolve(\App\Helpers\MarkdownParser::class)->text($expression)); ?>";
        });
        Blade::directive('markdownInline', function (string $expression) {
            return "<?php echo(resolve(\App\Helpers\MarkdownParser::class)->line($expression)); ?>";
        });
        Blade::directive('json', function (string $expression) {
            return '<?php echo(resolve(\App\Helpers\BladeHelper::class)->jsonSerialize('.$expression.')); ?>';
        });
        Blade::directive('date', function ($expression) {
            return '<?php echo(resolve(\App\Helpers\BladeHelper::class)->createTimeTag('.$expression.')); ?>';
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
