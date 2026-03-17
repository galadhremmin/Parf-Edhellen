<?php

namespace App\Http\Controllers\Api\v3;

use App\Helpers\StringHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\CrosswordCompletion;
use App\Models\CrosswordPuzzle;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrosswordApiController extends Controller
{
    /**
     * GET /api/games/crossword/{languageId}/{date}
     *
     * Returns the puzzle grid and clues (without answers).
     * If the user is authenticated, also returns their completion state.
     */
    public function puzzle(int $languageId, string $date): JsonResponse
    {
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

        // Strip answers from clues before sending to client.
        $clues = array_map(function (array $clue) {
            unset($clue['answer']);
            return $clue;
        }, $puzzle->clues ?? []);

        // Redact grid letter content — send null (black cell) vs "" (white cell) only.
        // The actual letters must not be sent to the client.
        $grid = array_map(function (array $row) {
            return array_map(fn ($cell) => $cell !== null ? '' : null, $row);
        }, $puzzle->grid ?? []);

        $response = [
            'puzzle_id'   => $puzzle->id,
            'date'        => $puzzle->puzzle_date->format('Y-m-d'),
            'language_id' => $puzzle->language_id,
            'grid'        => $grid,
            'clues'       => $clues,
            'completed'   => null,
            'days_completed' => null,
            'seconds_elapsed' => null,
        ];

        if (Auth::check()) {
            $completion = CrosswordCompletion::query()
                ->where('account_id', Auth::id())
                ->where('crossword_puzzle_id', $puzzle->id)
                ->first();

            if ($completion !== null) {
                $response['completed']       = true;
                $response['seconds_elapsed'] = $completion->seconds_elapsed;

                $response['days_completed'] = CrosswordCompletion::query()
                    ->join('crossword_puzzles', 'crossword_puzzles.id', '=', 'crossword_completions.crossword_puzzle_id')
                    ->where('crossword_completions.account_id', Auth::id())
                    ->where('crossword_puzzles.language_id', $puzzle->language_id)
                    ->count();
            } else {
                $response['completed'] = false;
            }
        }

        return response()->json($response);
    }

    /**
     * POST /api/games/crossword/check
     *
     * Validates the submitted cell entries against stored answers (server-side only).
     * Body: { puzzle_id: int, cells: { "row_col": letter }, seconds_elapsed?: int, is_assisted?: bool }
     * Returns: { results: { "row_col": bool }, completion: { ... } | null }
     *
     * If all expected cells are correct and the user is authenticated, a CrosswordCompletion
     * record is created (firstOrCreate — the first completion is authoritative; subsequent
     * calls with all-correct answers are silently ignored).
     */
    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'puzzle_id'       => ['required', 'integer', 'exists:crossword_puzzles,id'],
            'cells'           => ['required', 'array'],
            'cells.*'         => ['nullable', 'string', 'max:4'],
            'seconds_elapsed' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'is_assisted'     => ['boolean'],
        ]);

        $puzzle = CrosswordPuzzle::findOrFail($data['puzzle_id']);

        // Build expected cell map: "{row}:{col}" => normalised letter
        $expected = $this->buildExpectedCellMap($puzzle->clues ?? []);

        $submitted = $data['cells'];
        $results   = [];

        foreach ($expected as $cellKey => $normExpected) {
            $input = $submitted[$cellKey] ?? '';
            // Skip empty cells — only mark cells the user has actually filled in.
            if ($input === '' || $input === null) {
                continue;
            }
            $results[$cellKey] = $this->normalise($input) === $normExpected;
        }

        // Determine if ALL expected cells are present and correct.
        $allCorrect = count($expected) > 0
            && count($results) === count($expected)
            && ! in_array(false, $results, true);

        $completionPayload = null;

        if ($allCorrect && Auth::check()) {
            // firstOrCreate — the FIRST completion is authoritative; subsequent calls
            // with all-correct answers are silently ignored (no score updates).
            $completion = CrosswordCompletion::firstOrCreate(
                [
                    'account_id'          => Auth::id(),
                    'crossword_puzzle_id' => $puzzle->id,
                ],
                [
                    'seconds_elapsed' => $data['seconds_elapsed'] ?? null,
                    'is_assisted'     => $data['is_assisted'] ?? false,
                ]
            );

            $daysCompleted = CrosswordCompletion::query()
                ->join('crossword_puzzles', 'crossword_puzzles.id', '=', 'crossword_completions.crossword_puzzle_id')
                ->where('crossword_completions.account_id', Auth::id())
                ->where('crossword_puzzles.language_id', $puzzle->language_id)
                ->count();

            $completionPayload = [
                'days_completed'  => $daysCompleted,
                'seconds_elapsed' => $completion->seconds_elapsed,
                'is_assisted'     => $completion->is_assisted,
            ];
        }

        // Cells submitted that are not part of the puzzle are irrelevant; ignore them.
        return response()->json([
            'results'    => $results,
            'completion' => $completionPayload,
        ]);
    }

    /**
     * POST /api/games/crossword/reveal
     *
     * Returns the display-form answer for one clue and its cell positions.
     * Body: { puzzle_id: int, clue_number: int, direction: "across"|"down" }
     * Returns: { answer: string, cells: { "row_col": letter } }
     */
    public function reveal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'puzzle_id'   => ['required', 'integer', 'exists:crossword_puzzles,id'],
            'clue_number' => ['required', 'integer', 'min:1'],
            'direction'   => ['required', 'in:across,down'],
        ]);

        $puzzle = CrosswordPuzzle::findOrFail($data['puzzle_id']);

        $clue = collect($puzzle->clues ?? [])->first(function (array $c) use ($data) {
            return $c['number'] === $data['clue_number'] && $c['direction'] === $data['direction'];
        });

        if ($clue === null) {
            abort(404);
        }

        $answer = $clue['answer'];
        $cells  = $this->clueToLetterCells($clue);

        return response()->json(['answer' => $answer, 'cells' => $cells]);
    }

    /**
     * GET /api/games/crossword/{puzzleId}/admin-fill  (admin only)
     *
     * Returns the complete cell → letter map for all clues so an admin can
     * auto-fill the grid client-side for testing.
     */
    public function adminFill(int $puzzleId): JsonResponse
    {
        if (! Auth::check() || ! Auth::user()->isAdministrator()) {
            abort(403);
        }

        $puzzle  = CrosswordPuzzle::findOrFail($puzzleId);

        // Return display-form letters (not normalised), keyed by "row:col".
        $display = [];
        foreach ($puzzle->clues ?? [] as $clue) {
            foreach ($this->clueToLetterCells($clue) as $key => $letter) {
                $display[$key] = $letter;
            }
        }

        return response()->json(['cells' => $display]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Build a "{row}_{col}" => normalised letter map for all cells covered by any clue.
     *
     * @param  array<int, array<string, mixed>>  $clues
     * @return array<string, string>
     */
    private function buildExpectedCellMap(array $clues): array
    {
        $map = [];
        foreach ($clues as $clue) {
            foreach ($this->clueToLetterCells($clue) as $key => $letter) {
                $map[$key] = $this->normalise($letter);
            }
        }
        return $map;
    }

    /**
     * Map each letter of a clue's answer to its cell key "{row}_{col}".
     *
     * @param  array<string, mixed>  $clue
     * @return array<string, string>
     */
    private function clueToLetterCells(array $clue): array
    {
        $answer = (string) ($clue['answer'] ?? '');
        $row    = (int) $clue['row'];
        $col    = (int) $clue['col'];
        $across = $clue['direction'] === 'across';
        $dr     = $across ? 0 : 1;
        $dc     = $across ? 1 : 0;

        $letters = [];
        $len = mb_strlen($answer, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $key           = ($row + $i * $dr) . ':' . ($col + $i * $dc);
            $letters[$key] = mb_substr($answer, $i, 1, 'UTF-8');
        }
        return $letters;
    }

    private function normalise(string $input): string
    {
        // Use transformAccentsIntoLetters=false for 1-to-1 mapping: í → i (not ii).
        // This allows users to type "i" and match a grid cell whose answer is "í".
        return StringHelper::transliterate(trim($input), false);
    }
}
