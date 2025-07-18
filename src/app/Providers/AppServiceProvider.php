<?php

namespace App\Providers;

use App\Exceptions\DBHandler;
use App\Exceptions\Handler;
use App\Factories\DefaultSystemLanguageFactory;
use App\Helpers\ExternalGlossGroupToInternalUrlResolver;
use App\Helpers\MarkdownParserWrapper;
use App\Interfaces\IExternalToInternalUrlResolver;
use App\Interfaces\IMarkdownParser;
use App\Interfaces\ISystemLanguageFactory;
use App\Models\GlossGroup;
use App\Models\Initialization\Morphs;
use App\Repositories\AuditTrailRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Repositories\Noop\NoopAuditTrailRepository;
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
        
        $this->app->singleton(
            \Illuminate\Foundation\Exceptions\Handler::class,
            function ($app) {
                // DB logging isn't by design enabled in CLI
                $handlerType = ! $app->runningInConsole() && config('ed.system_errors_logging')
                    ? DBHandler::class
                    : Handler::class;
        
                return $app->make($handlerType);
            }
        );

        $this->app->singleton(
            ISystemLanguageFactory::class,
            DefaultSystemLanguageFactory::class
        );
        
        $this->app->singleton(
            IExternalToInternalUrlResolver::class,
            function () {
                $externalLinks = GlossGroup::whereNotNull('external_link_format')
                    ->orderBy('id')
                    ->get();
        
                return new ExternalGlossGroupToInternalUrlResolver($externalLinks);
            }
        );
        
        $this->app->singleton(
            IMarkdownParser::class,
            MarkdownParserWrapper::class
        );

        $this->app->singleton(
            IAuditTrailRepository::class,
            function ($app) {
        
                $handlerType = ! $app->runningInConsole()
                    ? AuditTrailRepository::class
                    : NoopAuditTrailRepository::class;
        
                return $app->make($handlerType);
            }
        );
    }
}
