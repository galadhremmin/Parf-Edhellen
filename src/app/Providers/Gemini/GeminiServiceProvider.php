<?php

namespace App\Providers\Gemini;

use App\Gemini\GeminiClueFacade;
use App\Interfaces\IRephrasesCrosswordClues;
use Illuminate\Support\ServiceProvider;

class GeminiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IRephrasesCrosswordClues::class, GeminiClueFacade::class);
    }
}
