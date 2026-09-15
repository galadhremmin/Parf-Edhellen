<?php

namespace App\Subscribers;

use App\Events\LexicalEntryCreated;
use App\Events\LexicalEntryDestroyed;
use App\Events\LexicalEntryEdited;
use App\Events\LexicalEntryInflectionsCreated;
use App\Jobs\ProcessLexicalEntryReindex;
use App\Jobs\ProcessSearchIndexCreation;
use App\Models\LexicalEntry;
use App\Repositories\SearchIndexRepository;

class GlossIndexerSubscriber
{
    private SearchIndexRepository $_searchIndexRepository;

    public function __construct(SearchIndexRepository $searchIndexRepository)
    {
        $this->_searchIndexRepository = $searchIndexRepository;
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
    {
        return [
            LexicalEntryCreated::class => 'onGlossCreated',
            LexicalEntryEdited::class => 'onGlossEdited',
            LexicalEntryInflectionsCreated::class => 'onGlossInflectionsCreated',
            LexicalEntryDestroyed::class => 'onGlossDestroyed',
        ];
    }

    public function onGlossCreated(LexicalEntryCreated $event): void
    {
        $this->reindex($event->lexicalEntry);
    }

    public function onGlossEdited(LexicalEntryEdited $event): void
    {
        $this->reindex($event->lexicalEntry);
    }

    public function onGlossInflectionsCreated(LexicalEntryInflectionsCreated $event): void
    {
        if ($event->incremental) {
            // Incremental are only adding to what's already there. This is useful when iteratively adding new indexes
            // although it comes with the downside that you have to manage the history (to avoid dead index links).
            $lexicalEntry = $event->lexicalEntry;
            foreach ($event->lexicalEntryInflections as $inflection) {
                ProcessSearchIndexCreation::dispatch($inflection->lexical_entry, $lexicalEntry->word, $lexicalEntry->language, //
                    $inflection->word)->onQueue('indexing');
            }
        } else {
            $this->reindex($event->lexicalEntry);
        }
    }

    public function onGlossDestroyed(LexicalEntryDestroyed $event): void
    {
        $this->delete($event->lexicalEntry);
    }

    public function reindex(LexicalEntry $lexicalEntry): void
    {
        ProcessLexicalEntryReindex::dispatch($lexicalEntry)->onQueue('indexing');
    }

    private function delete(LexicalEntry $lexicalEntry): void
    {
        $this->_searchIndexRepository->deleteAll($lexicalEntry);
    }
}
