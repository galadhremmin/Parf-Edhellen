<?php

namespace App\Repositories;

use App\Models\SearchDefinition;
use App\Models\SearchViewEvent;
use Carbon\Carbon;

class TrendingRepository
{
    public function getMostSearchedTerms(int $days = 7, int $limit = 10): array
    {
        $cutoff = Carbon::now()->subDays($days);

        $searchViewEvents = SearchViewEvent::query()
            ->where('viewed_at', '>=', $cutoff)
            ->selectRaw('search_id, COUNT(*) as view_count')
            ->groupBy('search_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->keyBy('search_id');

        if ($searchViewEvents->isEmpty()) {
            return [];
        }

        $definitions = SearchDefinition::query()
            ->leftJoin('languages', 'languages.id', '=', 'search_definitions.language_id')
            ->select('search_definitions.*', 'languages.short_name as language_short_name')
            ->whereIn('search_definitions.id', $searchViewEvents->keys())
            ->get()
            ->keyBy('id');

        return $searchViewEvents
            ->map(fn ($event) => [
                'search_term' => $definitions->get($event->search_id)->search_term,
                'language_id' => $definitions->get($event->search_id)->language_id,
                'language_short_name' => $definitions->get($event->search_id)->language_short_name,
                'speech_ids' => $this->parseSpeechIds($definitions->get($event->search_id)->speech_ids),
                'view_count' => $event->view_count,
            ])
            ->filter(fn ($item) => ! empty($item['search_term']))
            ->toArray();
    }

    private function parseSpeechIds(?string $speechIds): ?array
    {
        if (empty($speechIds)) {
            return null;
        }

        return array_map('intval', explode(',', $speechIds));
    }
}
