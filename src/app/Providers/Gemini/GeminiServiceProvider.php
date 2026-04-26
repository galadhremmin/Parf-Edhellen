<?php

namespace App\Providers\Gemini;

use App\ThirdParty\Gemini\GeminiClueFacade;
use App\ThirdParty\Gemini\GeminiPhrasesFacade;
use App\Interfaces\IIdentifiesPhrases;
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
        $this->app->bind(IIdentifiesPhrases::class, GeminiPhrasesFacade::class);
    }
}
