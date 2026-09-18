<?php

namespace App\Subscribers;

use App\Events\SentenceCreated;
use App\Events\SentenceDestroyed;
use App\Events\SentenceEdited;
use App\Events\SentenceFragmentsDestroyed;
use App\Jobs\ProcessSentenceReindex;
use App\Models\Sentence;
use App\Repositories\SearchIndexRepository;

class SentenceIndexerSubscriber
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
            SentenceCreated::class => 'onSentenceCreated',
            SentenceEdited::class => 'onSentenceEdited',
            SentenceDestroyed::class => 'onSentenceDestroyed',
            SentenceFragmentsDestroyed::class => 'onSentenceFragmentsDestroyed',
        ];
    }

    public function onSentenceCreated(SentenceCreated $event): void
    {
        $this->update($event->sentence);
    }

    public function onSentenceEdited(SentenceEdited $event): void
    {
        $this->update($event->sentence);
    }

    public function onSentenceDestroyed(SentenceDestroyed $event): void
    {
        $sentence = $event->sentence;

        foreach ($sentence->sentence_fragments as $fragment) {
            $this->_searchIndexRepository->deleteAll($fragment);
        }

        $this->_searchIndexRepository->deleteAll($sentence);
    }

    public function onSentenceFragmentsDestroyed(SentenceFragmentsDestroyed $event): void
    {
        foreach ($event->sentence_fragments as $fragment) {
            $this->_searchIndexRepository->deleteAll($fragment);
        }
    }

    private function update(Sentence $sentence): void
    {
        ProcessSentenceReindex::dispatch($sentence)->onQueue('indexing');
    }
}
