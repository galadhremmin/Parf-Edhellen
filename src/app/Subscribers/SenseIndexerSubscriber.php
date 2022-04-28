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

    /**
     * Refreshes search index for the provided sense by examining the keywords associated with
     * its glosses. This has the limitation that you cannot technically associate more keywords
     * to the sense than what is already associated with its glosses.
     * 
     * TODO: Do we even need sense anymore?
     */
    private function update(Sense $sense)
    {
        $existingIndex = $this->_searchIndexRepository->getForEntity($sense)->reduce(
            function (Collection $carry, $index) {
                $key = implode('|', [
                    $index->keyword,
                    $index->normalized_keyword,
                    $index->normalized_keyword_unaccented,
                    $index->normalized_keyword_reversed,
                    $index->normalized_keyword_reversed_unaccented,
                    $index->word
                ]);

                if (! $carry->has($key)) {
                    $carry->offsetSet($key, []);
                }

                $carry->offsetGet($key)[] = $index;
                return $carry;
            }, collect([])
        );

        $newIndex = $sense->glosses()->with('keywords')->where('is_deleted', 0)->get()->reduce(
            function (Collection $carry, $gloss) {
                foreach ($gloss->keywords as $keyword) {
                    $key = implode('|', [
                        $keyword->keyword,
                        $keyword->normalized_keyword,
                        $keyword->normalized_keyword_unaccented,
                        $keyword->normalized_keyword_reversed,
                        $keyword->normalized_keyword_reversed_unaccented,
                        $keyword->word
                    ]);
                    
                    if (! $carry->has($$key)) {
                        $carry->offsetSet($key, []);
                    }

                    $carry->offsetGet($key)[] = $keyword;
                }
                return $carry;
            }, collect([])
        );
        
        // determines which indexes to delete by retrieving the values in the original collection
        // that are not present in the given collection ($newIndex):
        $toRemove = $existingIndex->keys()->diff($newIndex->keys())->map(function ($key) use($existingIndex) {
            foreach ($existingIndex[$key] as $index) {
                yield $index->id;
            }
        });
        $this->_searchIndexRepository->deleteAllWithId($toRemove);

        $toAdd = $newIndex->keys()->diff($existingIndex->keys());
        foreach ($toAdd as $key) {
            $keyword = $newIndex->offsetGet($key)[0]; // We only need the first entity to resolve the word
            ProcessSearchIndexCreation::dispatch($sense, $keyword->wordEntity) //
                ->onQueue('indexing');
        }
    }
}
