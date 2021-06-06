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
use App\Models\Sense;
use App\Events\{
    SenseEdited
};
use App\Jobs\ProcessSearchIndexCreation;

class SenseIndexerSubscriber
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
            SenseEdited::class,
            self::class.'@onSenseEdited'
        );
    }

    public function onSenseEdited(SenseEdited $event)
    {
        $this->update($event->sense);
    }

    private function delete(Sense $sense)
    {
        $this->_searchIndexRepository->deleteAll($sense);
    }

    private function update(Sense $sense)
    {
        $this->delete($sense);
        foreach ($sense->keywords as $keyword) {
            ProcessSearchIndexCreation::dispatch($sense, $keyword->wordEntity) //
                ->onQueue('indexing');
        }
    }
}
