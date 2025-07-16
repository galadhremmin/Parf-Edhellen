<?php

namespace App\Providers;

use App\Helpers\BladeSsrHelper;
use Illuminate\Support\ServiceProvider;
use Spatie\Ssr\Engine;
use Spatie\Ssr\Engines\Node;
use Spatie\Ssr\Renderer;
use App\Resolvers\EdResolver;
use Illuminate\Support\Facades\Blade;

class SsrServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/ssr.php' => config_path('ssr.php'),
        ], 'config');

        Blade::directive('ssr', function (string $expression) {
            return "<?php echo app('ssr')->render($expression); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/ssr.php', 'ssr');

        $this->app->singleton(Node::class, function ($app) {
            return new Node(
                $app->config->get('ssr.node.node_path'),
                $app->config->get('ssr.node.temp_path')
            );
        });

        $this->app->bind(Engine::class, function ($app) {
            return $app->make($app->config->get('ssr.engine'));
        });

        $this->app->resolving(
            Renderer::class,
            function (Renderer $serverRenderer, $app) {
                return $serverRenderer
                    ->enabled($app->config->get('ssr.enabled'))
                    ->debug($app->config->get('ssr.debug'))
                    ->context('url', $app->request->getRequestUri())
                    ->context($app->config->get('ssr.context'))
                    ->env($app->config->get('ssr.env'))
                    ->resolveEntryWith(new EdResolver($app->config->get('ed.version')));
            }
        );

        $this->app->alias(BladeSsrHelper::class, 'ssr');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['ssr'];
    }
}
