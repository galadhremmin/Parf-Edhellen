<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\CrosswordCompletion;
use App\Models\CrosswordPuzzle;
use App\Models\GameCrosswordLanguage;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateInterval;
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
     * Calendar for a language: year and month in URL; default to current month when omitted.
     */
    public function calendar(Request $request, int $languageId, ?int $year = null, ?int $month = null): View|\Illuminate\Http\RedirectResponse
    {
        $gameLanguage = GameCrosswordLanguage::with('language')->find($languageId);
        if ($gameLanguage === null) {
            abort(404);
        }

        $now = Carbon::now();
        $resolvedYear  = $year  ?? $now->year;
        $resolvedMonth = $month ?? $now->month;

        if ($resolvedMonth < 1 || $resolvedMonth > 12) {
            abort(404);
        }
        if ($resolvedYear < 2000 || $resolvedYear > 2100) {
            abort(404);
        }

        if ($year === null || $month === null) {
            return redirect()->route('crossword.calendar', [
                'languageId' => $languageId,
                'year'       => $resolvedYear,
                'month'      => $resolvedMonth,
            ]);
        }

        $year  = $resolvedYear;
        $month = $resolvedMonth;

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();
        $today        = $now->startOfDay();

        $puzzles = CrosswordPuzzle::query()
            ->where('language_id', $languageId)
            ->whereBetween('puzzle_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->where('puzzle_date', '<=', $today->toDateString())
            ->orderBy('puzzle_date')
            ->get()
            ->keyBy(fn (CrosswordPuzzle $p) => $p->puzzle_date->format('Y-m-d'));

        $completedDates = [];
        if (Auth::check() && $puzzles->isNotEmpty()) {
            $completedDates = CrosswordCompletion::query()
                ->where('account_id', Auth::id())
                ->whereIn('crossword_puzzle_id', $puzzles->keys()->map(
                    fn (string $date) => $puzzles[$date]->id
                )->all())
                ->join('crossword_puzzles', 'crossword_completions.crossword_puzzle_id', '=', 'crossword_puzzles.id')
                ->pluck('crossword_puzzles.puzzle_date')
                ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
                ->all();
        }

        $prevMonth   = $startOfMonth->copy()->subMonth();
        $nextMonth   = $startOfMonth->copy()->addMonth();
        $canShowNext = $nextMonth->startOfDay()->lte($today);

        // Tomorrow's puzzle (may not be generated yet).
        $tomorrowStr       = $today->copy()->addDay()->format('Y-m-d');
        $hasTomorrowPuzzle = CrosswordPuzzle::query()
            ->where('language_id', $languageId)
            ->where('puzzle_date', $tomorrowStr)
            ->exists();

        // Consecutive-day streak for this language (walks backward from today across all time).
        $streak = ! Auth::check() ? 0 : //
            Cache::remember('ed.games.crosswords.streak.'.$request->user()->id.'.'.$languageId,
            DateInterval::createFromDateString('5 minutes'), function () use ($languageId, $today) {
                $allPuzzleDates = CrosswordPuzzle::query()
                    ->where('language_id', $languageId)
                    ->where('puzzle_date', '<=', $today->toDateString())
                    ->orderByDesc('puzzle_date')
                    ->pluck('puzzle_date')
                    ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
                    ->all();

                $allCompleted = CrosswordCompletion::query()
                    ->join('crossword_puzzles', 'crossword_puzzles.id', '=', 'crossword_completions.crossword_puzzle_id')
                    ->where('crossword_completions.account_id', Auth::id())
                    ->where('crossword_puzzles.language_id', $languageId)
                    ->pluck('crossword_puzzles.puzzle_date')
                    ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
                    ->all();

                $completedSet = array_flip($allCompleted);
                $streak = 0;
                foreach ($allPuzzleDates as $dateStr) {
                    if (isset($completedSet[$dateStr])) {
                        $streak++;
                    } else {
                        break;
                    }
                }

                return $streak;
            });

        $monthCompletedCount = count($completedDates);

        return view('crossword.calendar', [
            'gameLanguage'        => $gameLanguage,
            'year'                => $year,
            'month'               => $month,
            'startOfMonth'        => $startOfMonth,
            'endOfMonth'          => $endOfMonth,
            'today'               => $today,
            'tomorrowStr'         => $tomorrowStr,
            'hasTomorrowPuzzle'   => $hasTomorrowPuzzle,
            'puzzles'             => $puzzles,
            'completedDates'      => $completedDates,
            'monthCompletedCount' => $monthCompletedCount,
            'streak'              => $streak,
            'prevYear'            => $prevMonth->year,
            'prevMonth'           => $prevMonth->month,
            'nextYear'            => $nextMonth->year,
            'nextMonth'           => $nextMonth->month,
            'canShowNext'         => $canShowNext,
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

        $cells         = null;
        $completed     = null;
        $daysCompleted = null;
        $secondsElapsed = null;
        $isAssisted    = false;

        if (Auth::check()) {
            $completion = CrosswordCompletion::query()
                ->where('account_id', Auth::id())
                ->where('crossword_puzzle_id', $puzzle->id)
                ->first();

            if ($completion !== null) {
                $completed      = true;
                $secondsElapsed = $completion->seconds_elapsed;
                $isAssisted     = $completion->is_assisted;

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
            'puzzle_id'       => $puzzle->id,
            'date'            => $puzzle->puzzle_date->format('Y-m-d'),
            'language_id'     => $puzzle->language_id,
            'grid'            => $grid,
            'clues'           => $clues,
            'completed'       => $completed,
            'days_completed'  => $daysCompleted,
            'seconds_elapsed' => $secondsElapsed,
            'is_assisted'     => $isAssisted,
            'cells'           => $cells,
        ];

        return view('crossword.play', [
            'gameLanguage'   => $gameLanguage,
            'puzzle'         => $puzzle,
            'date'           => $date,
            'containerClass' => 'container-fluid',
            'initialState'   => $initialState,
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
            $row    = (int) $clue['row'];
            $col    = (int) $clue['col'];
            $across = ($clue['direction'] ?? '') === 'across';
            $dr     = $across ? 0 : 1;
            $dc     = $across ? 1 : 0;

            $len = mb_strlen($answer, 'UTF-8');
            for ($i = 0; $i < $len; $i++) {
                $key       = ($row + $i * $dr) . ':' . ($col + $i * $dc);
                $map[$key] = mb_substr($answer, $i, 1, 'UTF-8');
            }
        }
        return $map;
    }
}
