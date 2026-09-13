<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\CrosswordCompletion;
use App\Models\CrosswordPuzzle;
use App\Models\GameCrosswordLanguage;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateInterval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CrosswordController extends Controller
{
    /**
     * Languages that have at least one generated puzzle (visible to users).
     */
    public function index(): View
    {
        $languages = GameCrosswordLanguage::query()
            ->whereHas('puzzles')
            ->with('language')
            ->orderBy('title')
            ->get();

        return view('crossword.index', ['languages' => $languages]);
    }

    /**
     * A year of puzzles for a language, one cell per ISO week.
     *
     * Puzzles are generated weekly, so a day grid was mostly empty space: four
     * or five filled squares in a month of thirty. A year of 52 (sometimes 53)
     * week cells shows the same information at a glance, and a year's worth of
     * it.
     *
     * Where a week holds more than one puzzle -- which happens when the
     * generator is run by hand -- the latest one is the one the week links to.
     * The others stay playable at their own /play/{date} address; they are
     * simply not reachable from here.
     */
    public function calendar(Request $request, int $languageId, ?int $year = null): View|RedirectResponse
    {
        $gameLanguage = GameCrosswordLanguage::with('language')->find($languageId);
        if ($gameLanguage === null) {
            abort(404);
        }

        $now = Carbon::now();
        $resolvedYear = $year ?? $now->year;

        if ($resolvedYear < 2000 || $resolvedYear > 2100) {
            abort(404);
        }

        if ($year === null) {
            return redirect()->route('crossword.calendar', [
                'languageId' => $languageId,
                'year' => $resolvedYear,
            ]);
        }

        $year = $resolvedYear;
        $today = $now->copy()->startOfDay();

        // ISO weeks: Monday-based, and a year has 52 or 53 of them.
        $firstWeekStart = Carbon::create($year, 1, 4)->startOfWeek(Carbon::MONDAY);
        $weeksInYear = $firstWeekStart->isoWeeksInYear();
        $yearStart = $firstWeekStart->copy();
        $yearEnd = $firstWeekStart->copy()->addWeeks($weeksInYear)->subDay()->endOfDay();

        // Keyed by ISO week number, holding the latest puzzle of that week.
        $puzzlesByWeek = CrosswordPuzzle::query()
            ->where('language_id', $languageId)
            ->whereBetween('puzzle_date', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->where('puzzle_date', '<=', $today->toDateString())
            ->orderBy('puzzle_date')
            ->get()
            ->groupBy(fn (CrosswordPuzzle $puzzle) => $puzzle->puzzle_date->isoWeek())
            ->map(fn ($puzzlesInWeek) => $puzzlesInWeek->last());

        $completedWeeks = [];
        if (Auth::check() && $puzzlesByWeek->isNotEmpty()) {
            $completedWeeks = CrosswordCompletion::query()
                ->where('account_id', Auth::id())
                ->whereIn('crossword_puzzle_id', $puzzlesByWeek->map(fn (CrosswordPuzzle $p) => $p->id)->all())
                ->join('crossword_puzzles', 'crossword_completions.crossword_puzzle_id', '=', 'crossword_puzzles.id')
                ->pluck('crossword_puzzles.puzzle_date')
                ->map(fn ($date) => Carbon::parse($date)->isoWeek())
                ->all();
        }

        $weeks = [];
        for ($weekNumber = 1; $weekNumber <= $weeksInYear; $weekNumber++) {
            $weekStart = $firstWeekStart->copy()->addWeeks($weekNumber - 1);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            $puzzle = $puzzlesByWeek->get($weekNumber);

            $weeks[] = [
                'number' => $weekNumber,
                'start' => $weekStart,
                'end' => $weekEnd,
                'date' => $puzzle?->puzzle_date->format('Y-m-d'),
                'has_puzzle' => $puzzle !== null,
                'is_completed' => in_array($weekNumber, $completedWeeks, true),
                'is_current' => $today->between($weekStart, $weekEnd),
                'is_future' => $weekStart->gt($today),
            ];
        }

        // Consecutive weeks solved, walking backwards from the most recent puzzle.
        $streak = ! Auth::check() ? 0 : //
            Cache::remember('ed.games.crosswords.streak.'.$request->user()->id.'.'.$languageId,
                DateInterval::createFromDateString('5 minutes'), function () use ($languageId, $today) {
                    $allPuzzleWeeks = CrosswordPuzzle::query()
                        ->where('language_id', $languageId)
                        ->where('puzzle_date', '<=', $today->toDateString())
                        ->orderByDesc('puzzle_date')
                        ->pluck('puzzle_date')
                        ->map(fn ($date) => Carbon::parse($date)->format('o-W'))
                        ->unique()
                        ->values()
                        ->all();

                    $allCompleted = CrosswordCompletion::query()
                        ->join('crossword_puzzles', 'crossword_puzzles.id', '=', 'crossword_completions.crossword_puzzle_id')
                        ->where('crossword_completions.account_id', Auth::id())
                        ->where('crossword_puzzles.language_id', $languageId)
                        ->pluck('crossword_puzzles.puzzle_date')
                        ->map(fn ($date) => Carbon::parse($date)->format('o-W'))
                        ->all();

                    $completedSet = array_flip($allCompleted);
                    $streak = 0;
                    foreach ($allPuzzleWeeks as $week) {
                        if (isset($completedSet[$week])) {
                            $streak++;
                        } else {
                            break;
                        }
                    }

                    return $streak;
                });

        $availableCount = $puzzlesByWeek->count();
        $completedCount = count($completedWeeks);

        // Is there a puzzle waiting that is not playable yet?
        $nextPuzzleDate = CrosswordPuzzle::query()
            ->where('language_id', $languageId)
            ->where('puzzle_date', '>', $today->toDateString())
            ->orderBy('puzzle_date')
            ->value('puzzle_date');

        return view('crossword.calendar', [
            'gameLanguage' => $gameLanguage,
            'year' => $year,
            'weeks' => $weeks,
            'weeksInYear' => $weeksInYear,
            'today' => $today,
            'availableCount' => $availableCount,
            'completedCount' => $completedCount,
            'streak' => $streak,
            'nextPuzzleDate' => $nextPuzzleDate ? Carbon::parse($nextPuzzleDate) : null,
            'prevYear' => $year - 1,
            'nextYear' => $year + 1,
            'canShowNext' => $year < $now->year,
            'canShowPrev' => $year > 2000,
        ]);
    }

    /**
     * Play page for a specific puzzle (date).
     */
    public function show(int $languageId, string $date): View
    {
        $gameLanguage = GameCrosswordLanguage::with('language')->find($languageId);
        if ($gameLanguage === null) {
            abort(404);
        }

        try {
            $puzzleDate = Carbon::createFromFormat('Y-m-d', $date);
        } catch (InvalidFormatException) {
            abort(404);
        }

        $puzzle = CrosswordPuzzle::query()
            ->where('language_id', $languageId)
            ->where('puzzle_date', $puzzleDate->toDateString())
            ->first();

        if ($puzzle === null) {
            abort(404);
        }

        // Redact grid — send '' (white cell) vs null (black cell) only. No letters.
        $grid = array_map(
            fn (array $row) => array_map(fn ($cell) => $cell !== null ? '' : null, $row),
            $puzzle->grid ?? []
        );

        // Strip answers from clues before passing to client.
        $clues = array_map(function (array $clue) {
            unset($clue['answer']);

            return $clue;
        }, $puzzle->clues ?? []);

        $cells = null;
        $completed = null;
        $daysCompleted = null;
        $secondsElapsed = null;
        $isAssisted = false;

        if (Auth::check()) {
            $completion = CrosswordCompletion::query()
                ->where('account_id', Auth::id())
                ->where('crossword_puzzle_id', $puzzle->id)
                ->first();

            if ($completion !== null) {
                $completed = true;
                $secondsElapsed = $completion->seconds_elapsed;
                $isAssisted = $completion->is_assisted;

                // The existence of a CrosswordCompletion is proof the user solved the puzzle
                // correctly. It is therefore safe to regenerate and serve the correct answers.
                $cells = $this->buildCellMap($puzzle->clues ?? []);

                $daysCompleted = CrosswordCompletion::query()
                    ->join('crossword_puzzles', 'crossword_puzzles.id', '=', 'crossword_completions.crossword_puzzle_id')
                    ->where('crossword_completions.account_id', Auth::id())
                    ->where('crossword_puzzles.language_id', $puzzle->language_id)
                    ->count();
            } else {
                $completed = false;
            }
        }

        $initialState = [
            'puzzle_id' => $puzzle->id,
            'date' => $puzzle->puzzle_date->format('Y-m-d'),
            'language_id' => $puzzle->language_id,
            'grid' => $grid,
            'clues' => $clues,
            'completed' => $completed,
            'days_completed' => $daysCompleted,
            'seconds_elapsed' => $secondsElapsed,
            'is_assisted' => $isAssisted,
            'cells' => $cells,
        ];

        return view('crossword.play', [
            'gameLanguage' => $gameLanguage,
            'puzzle' => $puzzle,
            'date' => $date,
            'containerClass' => 'container-fluid',
            'initialState' => $initialState,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Build a "{row}:{col}" → display-form-letter map for all cells in the given clues.
     * Mirrors the logic of CrosswordApiController::clueToLetterCells.
     *
     * @param  array<int, array<string, mixed>>  $clues
     * @return array<string, string>
     */
    private function buildCellMap(array $clues): array
    {
        $map = [];
        foreach ($clues as $clue) {
            $answer = (string) ($clue['answer'] ?? '');
            $row = (int) $clue['row'];
            $col = (int) $clue['col'];
            $across = ($clue['direction'] ?? '') === 'across';
            $dr = $across ? 0 : 1;
            $dc = $across ? 1 : 0;

            $len = mb_strlen($answer, 'UTF-8');
            for ($i = 0; $i < $len; $i++) {
                $key = ($row + $i * $dr).':'.($col + $i * $dc);
                $map[$key] = mb_substr($answer, $i, 1, 'UTF-8');
            }
        }

        return $map;
    }
}
