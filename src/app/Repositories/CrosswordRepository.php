<?php

namespace App\Repositories;

use App\Models\CrosswordPuzzle;
use App\Models\GameCrosswordLanguage;
use Carbon\Carbon;

class CrosswordRepository
{
    /**
     * The current puzzle for each language: the most recent one playable today.
     *
     * Deliberately not "the puzzle dated today". Puzzles are generated weekly,
     * so on six days out of seven there is no puzzle bearing today's date and a
     * literal reading would leave the landing page empty almost all the time.
     * The current puzzle is simply the latest one that has been released.
     *
     * $withinDays guards against advertising a puzzle from months ago as this
     * week's if the generator has stopped running; past that horizon the caller
     * gets nothing and can say so honestly.
     *
     * @return array<int, array{language_id: int, title: string, description: ?string, date: string, is_this_week: bool}>
     */
    public function getCurrentPuzzles(int $withinDays = 14): array
    {
        $today = Carbon::today();
        $earliest = $today->copy()->subDays($withinDays);

        $puzzles = CrosswordPuzzle::query()
            ->whereBetween('puzzle_date', [$earliest->toDateString(), $today->toDateString()])
            ->orderBy('puzzle_date')
            ->get()
            ->groupBy('language_id')
            ->map(fn ($puzzlesForLanguage) => $puzzlesForLanguage->last());

        if ($puzzles->isEmpty()) {
            return [];
        }

        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);

        return GameCrosswordLanguage::query()
            ->whereIn('language_id', $puzzles->keys()->all())
            ->with('language')
            ->orderBy('title')
            ->get()
            ->map(function (GameCrosswordLanguage $language) use ($puzzles, $startOfWeek) {
                $puzzle = $puzzles->get($language->language_id);

                return [
                    'language_id' => (int) $language->language_id,
                    'title' => $language->getFriendlyName(),
                    'description' => $language->description,
                    'date' => $puzzle->puzzle_date->format('Y-m-d'),
                    'is_this_week' => $puzzle->puzzle_date->gte($startOfWeek),
                ];
            })
            ->values()
            ->toArray();
    }
}
