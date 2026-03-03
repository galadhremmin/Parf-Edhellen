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

        $searchIds = SearchViewEvent::query()
            ->where('viewed_at', '>=', $cutoff)
            ->selectRaw('search_id, COUNT(*) as view_count')
            ->groupBy('search_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->pluck('search_id');

        if ($searchIds->isEmpty()) {
            return [];
        }

        $definitions = SearchDefinition::query()
            ->whereIn('id', $searchIds)
            ->get()
            ->keyBy('id');

        return $searchIds
            ->map(fn ($id) => $definitions->get($id))
            ->filter()
            ->map(fn ($d) => [
                'search_term' => $d->search_term,
                'language_id' => $d->language_id,
                'speech_ids' => $this->parseSpeechIds($d->speech_ids),
            ])
            ->values()
            ->all();
    }

    private function parseSpeechIds(?string $speechIds): ?array
    {
        if (empty($speechIds)) {
            return null;
        }

        return array_map('intval', explode(',', $speechIds));
    }
}
