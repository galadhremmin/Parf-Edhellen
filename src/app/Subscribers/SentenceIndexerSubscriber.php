<?php

namespace App\Subscribers;

use App\Repositories\SearchIndexRepository;
use App\Models\{
    Sentence
};
use App\Events\{
    SentenceCreated,
    SentenceDestroyed,
    SentenceEdited
};
use App\Helpers\SentenceBuilders\SentenceBuilder;
use App\Helpers\StringHelper;
use App\Jobs\ProcessSearchIndexCreation;

class SentenceIndexerSubscriber
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
            SentenceCreated::class,
            self::class.'@onSentenceCreated'
        );
        $events->listen(
            SentenceEdited::class,
            self::class.'@onSentenceEdited'
        );
        $events->listen(
            SentenceDestroyed::class,
            self::class.'@onSentenceDestroyed'
        );
    }

    public function onSentenceCreated(SentenceCreated $event)
    {
        $this->update($event->sentence);
    }

    public function onSentenceEdited(SentenceEdited $event)
    {
        $this->update($event->sentence);
    }

    public function onSentenceDestroyed(SentenceDestroyed $event)
    {
        $sentence = $event->sentence;

        foreach ($sentence->sentence_fragments as $fragment) {
            $this->_searchIndexRepository->deleteAll($fragment);
        }
        
        $this->_searchIndexRepository->deleteAll($sentence);
    }

    private function update(Sentence $sentence)
    {
        foreach ($sentence->sentence_fragments as $fragment) {
            if ($fragment->type === SentenceBuilder::TYPE_CODE_WORD) {
                $word = $fragment->gloss->word;
                $inflection = StringHelper::toLower($fragment->fragment);

                if ($inflection === StringHelper::toLower($word->word)) {
                    $inflection = null; // if the words are identical, don't consider the fragment an inflection
                }

                ProcessSearchIndexCreation::dispatch($fragment, $word, $inflection) //
                    ->onQueue('indexing');
            }
        }
    }
}
