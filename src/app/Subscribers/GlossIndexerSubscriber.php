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
    Account,
    Gloss
};
use App\Events\{
    GlossCreated,
    GlossDestroyed,
    GlossEdited
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
            GlossDestroyed::class,
            self::class.'@onGlossDestroyed'
        );
        $events->listen(
            GlossEdited::class,
            self::class.'@onGlossEdited'
        );
    }

    public function onGlossCreated(GlossCreated $event)
    {
        $this->update($event->gloss);
    }

    public function onGlossDestroyed(GlossDestroyed $event)
    {
        $this->delete($event->gloss);
    }

    public function onGlossEdited(GlossEdited $event)
    {
        $this->update($event->gloss);
    }

    private function delete(Gloss $gloss)
    {
        $this->_searchIndexRepository->deleteAll($gloss);
    }

    private function update(Gloss $gloss)
    {
        $this->delete($gloss->id);
        foreach ($gloss->keywords as $keyword) {
            ProcessSearchIndexCreation::dispatch($gloss, $keyword->wordEntity, $keyword->keyword) //
                ->onQueue('indexing');
        }
    }
}
