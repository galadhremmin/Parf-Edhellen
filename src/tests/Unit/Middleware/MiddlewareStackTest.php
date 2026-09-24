<?php

namespace Tests\Unit\Middleware;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Collection;
use ReflectionProperty;
use Tests\TestCase;

class MiddlewareStackTest extends TestCase
{
    /**
     * Laravel runs a handful of middleware globally, ahead of every group. Repeating one of them in a
     * group is not merely wasteful: `PreventRequestsDuringMaintenance` listed after `EncryptCookies`
     * re-decides maintenance mode on a bypass cookie that EncryptCookies has already discarded (it is
     * signed, not encrypted, because it is written before the app boots), so `down --with-secret` lets
     * nobody through to a web page.
     */
    public function test_no_group_repeats_a_global_middleware()
    {
        $global = $this->globalMiddleware();

        foreach ($this->app['router']->getMiddlewareGroups() as $name => $middleware) {
            foreach (array_filter($middleware, 'is_string') as $entry) {
                $repeated = $global->first(fn (string $g) => is_a($entry, $g, /* allow_string = */ true));

                $this->assertNull($repeated, "The {$name} group repeats {$repeated}, which already runs globally.");
            }
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function globalMiddleware(): Collection
    {
        $kernel = $this->app->make(Kernel::class);
        $middleware = new ReflectionProperty($kernel, 'middleware');

        return collect($middleware->getValue($kernel))->filter(fn ($middleware) => is_string($middleware))->values();
    }
}
