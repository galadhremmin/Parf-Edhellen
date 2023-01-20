<?php

namespace App\Subscribers;

use Illuminate\Support\Collection;
use App\Repositories\{
    SearchIndexRepository,
    WordRepository
};
use App\Models\{
    Gloss,
    Language
};
use App\Events\{
    GlossCreated,
    GlossDestroyed,
    GlossEdited,
    GlossInflectionsCreated
};
use App\Jobs\ProcessSearchIndexCreation;

class GlossIndexerSubscriber
{
    private $_searchIndexRepository;
    private $_wordRepository;
    private $_englishLanguage;

    public function __construct(SearchIndexRepository $searchIndexRepository, WordRepository $wordRepository)
    {
        $this->_searchIndexRepository = $searchIndexRepository;
        $this->_wordRepository = $wordRepository;
        $this->_englishLanguage = Language::where('name', 'English')->firstOrFail();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            GlossCreated::class,
            self::class.'@onGlossCreated'
        );
        $events->listen(
            GlossEdited::class,
            self::class.'@onGlossEdited'
        );
        $events->listen(
            GlossInflectionsCreated::class,
            self::class.'@onGlossInflectionsCreated'
        );
        $events->listen(
            GlossDestroyed::class,
            self::class.'@onGlossDestroyed'
        );
    }

    public function onGlossCreated(GlossCreated $event)
    {
        $this->update($event->gloss, $event->gloss->gloss_inflections);
    }

    public function onGlossEdited(GlossEdited $event)
    {
        $this->update($event->gloss, $event->gloss->gloss_inflections);
    }

    public function onGlossInflectionsCreated(GlossInflectionsCreated $event)
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

    public function onGlossDestroyed(GlossDestroyed $event)
    {
        $this->delete($event->gloss);
    }

    private function update(Gloss $gloss, Collection $inflections)
    {
        $this->delete($gloss);

        $translations = $gloss->translations->map(function ($t) {
            return $t->translation;
        });

        foreach ($gloss->keywords as $keyword) {
            if (! $translations->contains($keyword->keyword)) {
                ProcessSearchIndexCreation::dispatch($gloss, $keyword->wordEntity, $keyword->keyword_language, $keyword->keyword) //
                    ->onQueue('indexing');
            }
        }

        foreach ($translations as $translation) {
            $translationWord = $this->_wordRepository->save($translation, $gloss->account_id);
            ProcessSearchIndexCreation::dispatch($gloss, $translationWord, $this->_englishLanguage) //
                ->onQueue('indexing');
        }

        foreach ($inflections as $inflection) {
            ProcessSearchIndexCreation::dispatch($gloss, $gloss->word, $gloss->language, $inflection->word) //
                ->onQueue('indexing');
        }
    }

    private function delete(Gloss $gloss)
    {
        $this->_searchIndexRepository->deleteAll($gloss);
    }
}
