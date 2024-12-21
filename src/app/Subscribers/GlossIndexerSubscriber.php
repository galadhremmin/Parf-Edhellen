<?php

namespace App\Subscribers;

use App\Events\GlossCreated;
use App\Events\GlossDestroyed;
use App\Events\GlossEdited;
use App\Events\GlossInflectionsCreated;
use App\Interfaces\ISystemLanguageFactory;
use App\Jobs\ProcessSearchIndexCreation;
use App\Models\Gloss;
use App\Models\Language;
use App\Repositories\SearchIndexRepository;
use App\Repositories\WordRepository;
use Illuminate\Support\Collection;

class GlossIndexerSubscriber
{
    private SearchIndexRepository $_searchIndexRepository;

    private WordRepository $_wordRepository;

    private ?Language $_systemLanguage;

    public function __construct(SearchIndexRepository $searchIndexRepository, WordRepository $wordRepository,
        ISystemLanguageFactory $systemLanguageFactory)
    {
        $this->_searchIndexRepository = $searchIndexRepository;
        $this->_wordRepository = $wordRepository;
        $this->_systemLanguage = $systemLanguageFactory->language();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        return [
            GlossCreated::class => 'onGlossCreated',
            GlossEdited::class => 'onGlossEdited',
            GlossInflectionsCreated::class => 'onGlossInflectionsCreated',
            GlossDestroyed::class => 'onGlossDestroyed',
        ];
    }

    public function onGlossCreated(GlossCreated $event): void
    {
        $this->update($event->gloss, $event->gloss->gloss_inflections);
    }

    public function onGlossEdited(GlossEdited $event): void
    {
        $this->update($event->gloss, $event->gloss->gloss_inflections);
    }

    public function onGlossInflectionsCreated(GlossInflectionsCreated $event): void
    {
        if ($event->incremental) {
            // Incremental are only adding to what's already there. This is useful when iteratively adding new indexes
            // although it comes with the downside that you have to manage the history (to avoid dead index links).
            $gloss = $event->gloss;
            foreach ($event->gloss_inflections as $inflection) {
                ProcessSearchIndexCreation::dispatch($inflection->gloss, $gloss->word, $gloss->language, //
                    $inflection->word)->onQueue('indexing');
            }
        } else {
            $this->update($event->gloss, $event->gloss_inflections);
        }
    }

    public function onGlossDestroyed(GlossDestroyed $event): void
    {
        $this->delete($event->gloss);
    }

    private function update(Gloss $gloss, Collection $inflections): void
    {
        $this->delete($gloss);

        $translations = $gloss->translations->map(function ($t) {
            return $t->translation;
        });

        foreach ($gloss->keywords as $keyword) {
            if (! $translations->contains($keyword->keyword)) {
                $keywordLanguage = $keyword->keyword_language ?: $this->_systemLanguage;
                ProcessSearchIndexCreation::dispatch($gloss, $keyword->wordEntity, $keywordLanguage, $keyword->keyword) //
                    ->onQueue('indexing');
            }
        }

        foreach ($translations as $translation) {
            $translationWord = $this->_wordRepository->save($translation, $gloss->account_id);
            ProcessSearchIndexCreation::dispatch($gloss, $translationWord, $this->_systemLanguage) //
                ->onQueue('indexing');
        }

        foreach ($inflections as $inflection) {
            ProcessSearchIndexCreation::dispatch($gloss, $gloss->word, $gloss->language, $inflection->word) //
                ->onQueue('indexing');
        }
    }

    private function delete(Gloss $gloss): void
    {
        $this->_searchIndexRepository->deleteAll($gloss);
    }
}
