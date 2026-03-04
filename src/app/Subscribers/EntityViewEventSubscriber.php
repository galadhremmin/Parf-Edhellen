<?php

namespace App\Subscribers;

use App\Events\EntityViewed;
use App\Jobs\RecordSearchView;
use App\Models\SearchKeyword;

class EntityViewEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(): array
    {
        return [
            EntityViewed::class => 'onEntityViewed',
        ];
    }

    public function onEntityViewed(EntityViewed $event): void
    {
        if ((int) $event->groupId !== SearchKeyword::SEARCH_GROUP_DICTIONARY) {
            return;
        }

        $sections = $event->entities['entities']['sections'] ?? [];
        if (count($sections) < 1) {
            return;
        }

        $searchTerm = trim($event->entities['word'] ?? '');
        if ($searchTerm === '') {
            return;
        }

        RecordSearchView::dispatch($event->searchValue, $searchTerm);
    }
}
