<?php

namespace App\Subscribers;

use Aws\Credentials\{
    AssumeRoleCredentialProvider,
    CredentialProvider,
    InstanceProfileProvider
};
use Aws\Sts\StsClient;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

use App\Interfaces\IIdentifiesPhrases;
use App\Repositories\{
    SearchIndexRepository,
    WordRepository
};
use App\Models\Initialization\Morphs;
use App\Models\{
    Sentence
};
use App\Events\{
    SentenceCreated,
    SentenceDestroyed,
    SentenceEdited
};
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
            SentenceDestroyed::class,
            self::class.'@onSentenceDestroyed'
        );
        $events->listen(
            SentenceEdited::class,
            self::class.'@onSentenceEdited'
        );
    }

    public function onSentenceCreated(SentenceCreated $event)
    {
        $this->update($event->sentence);
    }

    public function onSentenceDestroyed(SentenceDestroyed $event)
    {
        $sentence = $event->sentence;
        $this->_searchIndexRepository->deleteAll($fragment);
    }

    public function onSentenceEdited(SentenceEdited $event)
    {
        $this->update($event->sentence);
    }

    private function delete(Sentence $sentence)
    {
        foreach ($sentence->sentence_fragments as $fragment) {
            $this->_searchIndexRepository->deleteAll($fragment);
        }
    }

    private function update(Sentence $sentence)
    {
        foreach ($sentence->sentence_fragments as $fragment) {
            foreach ($fragment->keywords as $keyword) {
                ProcessSearchIndexCreation::dispatch($fragment, $keyword->wordEntity, $keyword->keyword) //
                    ->onQueue('indexing');
            }
        }
    }
}
