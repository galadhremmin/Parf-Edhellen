<?php

namespace App\Repositories;

use App\Models\SearchDefinition;
use App\Models\SearchViewEvent;
use App\Models\SearchViewHourlyStat;
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

    /**
     * Average number of searches performed per day over a trailing window.
     *
     * Deliberately an average rather than "today": a figure for today reads
     * as near zero every morning, which is both misleading and unflattering.
     * Returns 0 when there is nothing recorded yet, and callers are expected
     * to omit the figure entirely in that case rather than print a zero.
     */
    public function getAverageSearchesPerDay(int $days = 30): int
    {
        if ($days < 1) {
            return 0;
        }

        $searches = SearchViewEvent::query()
            ->where('viewed_at', '>=', Carbon::now()->subDays($days))
            ->count();

        return (int) round($searches / $days);
    }

    public function getViewsPerHour(Carbon $from, Carbon $to): array
    {
        return SearchViewHourlyStat::query()
            ->whereBetween('hour', [$from, $to])
            ->orderBy('hour')
            ->get()
            ->map(fn (SearchViewHourlyStat $stat) => [
                'date' => $stat->hour->format('Y-m-d H:00'),
                'count' => $stat->views,
            ])
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
