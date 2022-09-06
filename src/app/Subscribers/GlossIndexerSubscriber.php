<?php

namespace App\Subscribers;

use Illuminate\Support\Collection;
use App\Repositories\{
    SearchIndexRepository
};
use App\Models\{
    Gloss
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

    public function __construct(SearchIndexRepository $searchIndexRepository)
    {
        $this->_searchIndexRepository = $searchIndexRepository;
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
            foreach ($event->gloss_inflections as $inflection) {
                ProcessSearchIndexCreation::dispatch($inflection->gloss, $inflection->gloss->word, $inflection->word) //
                    ->onQueue('indexing');
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

        foreach ($gloss->keywords as $keyword) {
            ProcessSearchIndexCreation::dispatch($gloss, $keyword->wordEntity, $keyword->keyword) //
                ->onQueue('indexing');
        }

        foreach ($inflections as $inflection) {
            ProcessSearchIndexCreation::dispatch($inflection->gloss, $inflection->gloss->word, $inflection->word) //
                ->onQueue('indexing');
        }
    }

    private function delete(Gloss $gloss)
    {
        $this->_searchIndexRepository->deleteAll($gloss);
    }
}
