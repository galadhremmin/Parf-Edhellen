<?php

namespace App\Subscribers;

use App\Events\SentenceCreated;
use App\Events\SentenceDestroyed;
use App\Events\SentenceEdited;
use App\Events\SentenceFragmentsDestroyed;
use App\Helpers\SentenceBuilders\SentenceBuilder;
use App\Helpers\StringHelper;
use App\Jobs\ProcessSearchIndexCreation;
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
        foreach ($sentence->sentence_fragments as $fragment) {
            if ($fragment->type === SentenceBuilder::TYPE_CODE_WORD) {
                $word = $fragment->gloss->word;
                $inflection = StringHelper::toLower($fragment->fragment);

                if ($inflection === StringHelper::toLower($word->word)) {
                    $inflection = null; // if the words are identical, don't consider the fragment an inflection
                }

                ProcessSearchIndexCreation::dispatch($fragment, $word, $fragment->gloss->language, $inflection) //
                    ->onQueue('indexing');
            }
        }
    }
}
