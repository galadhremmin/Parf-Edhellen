<?php

namespace App\Services;

use DateInterval;
use App\Helpers\StringHelper;
use App\Models\CrosswordPuzzle;
use App\Models\GameCrosswordLanguage;
use App\Models\GameCrosswordLexicalEntryGroup;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrosswordPuzzleGenerator
{
    private const MIN_WORD_LENGTH = 3;
    private const MAX_WORD_LENGTH = 12;
    private const TARGET_WORDS    = 8;
    private const MIN_PLACED      = 8;    // reject grids with fewer words than this
    private const MAX_ATTEMPTS    = 50;   // stochastic restart budget

    /**
     * Fetch word/clue pairs for a language from LexicalEntry → Word + Gloss.
     *
     * We fetch TARGET_WORDS × 5 candidates so each restart attempt gets enough
     * variety when shuffling, without hammering the database multiple times.
     *
     * @return array<int, array{word: string, clue: string, normalized: string, letters: string[], normLetters: string[]}>
     */
    public function fetchWordCluePairs(int $languageId, int $targetWords = self::TARGET_WORDS): array
    {
        $groupIds = $this->getLexicalEntryGroupIds();
        if (empty($groupIds)) {
            return [];
        }

        $rows = LexicalEntry::active()
            ->join('words', 'words.id', 'lexical_entries.word_id')
            ->join('glosses', 'glosses.lexical_entry_id', 'lexical_entries.id')
            ->where('language_id', $languageId)
            ->whereIn('lexical_entry_group_id', $groupIds)
            ->where(DB::raw('CHAR_LENGTH(words.normalized_word)'), '>=', self::MIN_WORD_LENGTH)
            ->where(DB::raw('CHAR_LENGTH(words.normalized_word)'), '<=', self::MAX_WORD_LENGTH)
            ->whereColumn('glosses.translation', '<>', 'words.word')
            ->whereNotNull('words.word')
            ->where('words.word', '!=', '')
            ->where('words.word', 'not like', '% %')
            ->inRandomOrder()
            ->limit($targetWords * 5)
            ->select('words.word as word', 'glosses.translation as gloss', 'words.normalized_word as normalized')
            ->get();

        $seen  = [];
        $pairs = [];
        foreach ($rows as $row) {
            // Clean the word before anything else.
            // NFC-normalize so NFD-encoded words have consistent code-point counts.
            $word = $this->cleanWord(trim((string) $row->word));
            $word = \Normalizer::normalize($word, \Normalizer::FORM_C) ?: $word;
            $norm = $this->normalizeForMatching($word);
            if ($word === '' || mb_strlen($norm) < self::MIN_WORD_LENGTH || mb_strlen($norm) > self::MAX_WORD_LENGTH) {
                continue;
            }
            if (isset($seen[$norm])) {
                continue;
            }
            $seen[$norm] = true;
            $pairs[] = [
                'word'        => $word,
                'clue'        => trim((string) $row->gloss),
                'normalized'  => $norm,
                'letters'     => $this->mbSplit($word),
                'normLetters' => $this->mbSplit($norm),
            ];
        }

        return $pairs;
    }

    /**
     * Build the best grid found across MAX_ATTEMPTS stochastic restarts.
     *
     * Each attempt shuffles the word list (keeping longest first only within
     * the attempt), places words greedily, enforces crossword validity rules,
     * and scores the result. The highest-scoring valid grid wins.
     *
     * @param  array<int, array{word: string, clue: string, normalized: string, letters: string[], normLetters: string[]}>  $pairs
     * @return array{grid: array<int, array<int, string|null>>, clues: array<int, array<string, mixed>>}|null
     */
    public function buildGrid(array $pairs): ?array
    {
        if (count($pairs) < 2) {
            return null;
        }

        $bestResult = null;
        $bestScore  = PHP_INT_MIN;

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            // Shuffle, then stable-sort longest-first so long words still anchor the grid.
            shuffle($pairs);
            usort($pairs, fn ($a, $b) => count($b['normLetters']) - count($a['normLetters']));

            $placed = $this->attemptPlacement($pairs);
            if ($placed === null) {
                continue;
            }

            $score = $this->scoreGrid($placed);
            if ($score > $bestScore) {
                $bestScore  = $score;
                $bestResult = $placed;
            }
        }

        if ($bestResult === null) {
            return null;
        }

        [$grid, $minR, $minC] = $this->cellsToGrid($bestResult['cells']);
        $clues = $this->buildClues($bestResult['placed'], $minR, $minC);

        return ['grid' => $grid, 'clues' => $clues];
    }

    /**
     * Generate and persist a puzzle for the given language and date. Returns the CrosswordPuzzle or null.
     */
    public function generateForLanguageAndDate(int $languageId, Carbon $puzzleDate, int $targetWords = self::TARGET_WORDS): ?CrosswordPuzzle
    {
        $dateStr = $puzzleDate->toDateString();

        $exists = CrosswordPuzzle::query()
            ->where('language_id', $languageId)
            ->where('puzzle_date', $dateStr)
            ->exists();
        if ($exists) {
            return null;
        }

        $pairs = $this->fetchWordCluePairs($languageId, $targetWords);
        if (empty($pairs)) {
            Log::error('CrosswordPuzzleGenerator: no word/clue pairs available', [
                'language_id' => $languageId,
                'date'        => $dateStr,
            ]);
            return null;
        }

        $result = $this->buildGrid($pairs);
        if ($result === null) {
            Log::error('CrosswordPuzzleGenerator: grid generation failed after all attempts', [
                'language_id'     => $languageId,
                'date'            => $dateStr,
                'pairs_available' => count($pairs),
                'max_attempts'    => self::MAX_ATTEMPTS,
            ]);
            return null;
        }

        return CrosswordPuzzle::create([
            'language_id' => $languageId,
            'puzzle_date' => $dateStr,
            'grid'        => $result['grid'],
            'clues'       => $result['clues'],
        ]);
    }

    /**
     * Generate daily puzzles for all enabled languages (target date = today).
     *
     * @return array<int, CrosswordPuzzle|null> language_id => puzzle or null
     */
    public function generateDaily(): array
    {
        $today     = Carbon::now()->startOfDay();
        $languages = GameCrosswordLanguage::pluck('language_id');
        $results   = [];
        foreach ($languages as $languageId) {
            $results[(int) $languageId] = $this->generateForLanguageAndDate((int) $languageId, $today);
        }
        return $results;
    }

    // ─── Single placement attempt ──────────────────────────────────────────────

    /**
     * One greedy placement pass over the word list.
     *
     * Returns ['cells' => ..., 'placed' => ...] when the result meets minimum
     * quality requirements (MIN_PLACED words, fully connected), or null.
     *
     * @param  array<int, array<string, mixed>>  $pairs
     * @return array{cells: array<string, string>, placed: array<int, array<string, mixed>>}|null
     */
    private function attemptPlacement(array $pairs): ?array
    {
        $cells  = [];  // "{row}_{col}" => display letter
        $norms  = [];  // "{row}_{col}" => normalised letter
        $placed = [];

        // First word anchors the grid horizontally at the origin.
        $first = $pairs[0];
        $this->placeLetters($cells, $norms, 0, 0, true, $first['letters'], $first['normLetters']);
        $placed[] = [
            'row' => 0, 'col' => 0, 'across' => true,
            'letters' => $first['letters'], 'normLetters' => $first['normLetters'],
            'clue' => $first['clue'], 'word' => $first['word'],
        ];

        for ($i = 1; $i < count($pairs); $i++) {
            $entry = $this->tryPlaceWord($cells, $norms, $placed, $pairs[$i]);
            if ($entry !== null) {
                $placed[] = $entry;
            }
        }

        if (count($placed) < self::MIN_PLACED) {
            return null;
        }

        if (! $this->allWordsConnected($placed)) {
            return null;
        }

        return ['cells' => $cells, 'placed' => $placed];
    }

    // ─── Placement primitives ──────────────────────────────────────────────────

    /**
     * Try to place a word by crossing any already-placed word.
     * Returns the placement entry on success, or null.
     *
     * @param  array<string, string>             $cells
     * @param  array<string, string>             $norms
     * @param  array<int, array<string, mixed>>  $placed
     * @param  array<string, mixed>              $pair
     * @return array<string, mixed>|null
     */
    private function tryPlaceWord(array &$cells, array &$norms, array $placed, array $pair): ?array
    {
        foreach ($placed as $p) {
            $newAcross = ! $p['across'];

            foreach ($pair['normLetters'] as $wi => $newNorm) {
                foreach ($p['normLetters'] as $ci => $existingNorm) {
                    if ($newNorm !== $existingNorm) {
                        continue;
                    }

                    // Crossing cell: letter $ci of the already-placed word.
                    $crossR = $p['row'] + $ci * ($p['across'] ? 0 : 1);
                    $crossC = $p['col'] + $ci * ($p['across'] ? 1 : 0);

                    // Start of new word so letter $wi lands on the crossing cell.
                    $newRow = $crossR - $wi * ($newAcross ? 0 : 1);
                    $newCol = $crossC - $wi * ($newAcross ? 1 : 0);

                    if ($this->canPlace($cells, $norms, $newRow, $newCol, $newAcross, $pair['normLetters'])) {
                        $this->placeLetters($cells, $norms, $newRow, $newCol, $newAcross, $pair['letters'], $pair['normLetters']);
                        return [
                            'row'         => $newRow,
                            'col'         => $newCol,
                            'across'      => $newAcross,
                            'letters'     => $pair['letters'],
                            'normLetters' => $pair['normLetters'],
                            'clue'        => $pair['clue'],
                            'word'        => $pair['word'],
                        ];
                    }
                }
            }
        }
        return null;
    }

    /**
     * Check whether a word can legally be placed at (row, col) in the given direction.
     *
     * Rules enforced:
     *  1. The cell immediately before the word start must be empty (no word merging).
     *  2. The cell immediately after the word end must be empty (no word merging).
     *  3. Each occupied cell must contain the matching normalised letter.
     *  4. Each empty (non-crossing) cell must have no perpendicular neighbours
     *     (no parallel words running side-by-side).
     *  5. The word must cross at least one already-placed cell.
     */
    private function canPlace(array $cells, array $norms, int $row, int $col, bool $across, array $normLetters): bool
    {
        $len  = count($normLetters);
        $dr   = $across ? 0 : 1;
        $dc   = $across ? 1 : 0;
        $prDr = $across ? 1 : 0;  // perpendicular direction
        $prDc = $across ? 0 : 1;

        // Rule 1: end-cap before the word.
        if (isset($cells[($row - $dr) . '_' . ($col - $dc)])) {
            return false;
        }
        // Rule 2: end-cap after the word.
        if (isset($cells[($row + $len * $dr) . '_' . ($col + $len * $dc)])) {
            return false;
        }

        $intersections = 0;
        for ($i = 0; $i < $len; $i++) {
            $r   = $row + $i * $dr;
            $c   = $col + $i * $dc;
            $key = "{$r}_{$c}";

            if (isset($norms[$key])) {
                // Rule 3: existing letter must match.
                if ($norms[$key] !== $normLetters[$i]) {
                    return false;
                }
                $intersections++;
            } else {
                // Rule 4: no parallel neighbours for non-crossing cells.
                if (isset($cells[($r + $prDr) . '_' . ($c + $prDc)]) ||
                    isset($cells[($r - $prDr) . '_' . ($c - $prDc)])) {
                    return false;
                }
            }
        }

        // Rule 5: must intersect at least one existing word.
        return $intersections > 0;
    }

    private function placeLetters(array &$cells, array &$norms, int $row, int $col, bool $across, array $letters, array $normLetters): void
    {
        $dr = $across ? 0 : 1;
        $dc = $across ? 1 : 0;
        foreach ($letters as $i => $letter) {
            $key         = ($row + $i * $dr) . '_' . ($col + $i * $dc);
            $cells[$key] = $letter;
            $norms[$key] = $normLetters[$i];
        }
    }

    // ─── Grid quality ─────────────────────────────────────────────────────────

    /**
     * Score a placement result. Higher is better.
     *
     * Scoring components:
     *  +10 per placed word   (maximise coverage)
     *  +4  per crossing      (dense grids are more interesting)
     *  -20 per isolated word (should be impossible given canPlace rule 5, but guard anyway)
     *  -1  per unit of bounding-box area per word (prefer compact grids)
     *
     * @param  array{cells: array<string, string>, placed: array<int, array<string, mixed>>}  $result
     */
    private function scoreGrid(array $result): int
    {
        $placed   = $result['placed'];
        $cells    = $result['cells'];
        $n        = count($placed);
        $crossings = 0;
        $isolated  = 0;

        // Count shared cells (each occupied cell shared by exactly 2 words = 1 crossing).
        // A cell is a crossing when both an across and a down word pass through it.
        // We detect this by counting cells where two placed words overlap.
        $cellWordCount = [];
        foreach ($placed as $p) {
            $dr = $p['across'] ? 0 : 1;
            $dc = $p['across'] ? 1 : 0;
            foreach (array_keys($p['normLetters']) as $i) {
                $key = ($p['row'] + $i * $dr) . '_' . ($p['col'] + $i * $dc);
                $cellWordCount[$key] = ($cellWordCount[$key] ?? 0) + 1;
            }
        }
        foreach ($cellWordCount as $count) {
            if ($count >= 2) {
                $crossings++;
            }
        }

        // Words with zero crossings (should not happen given canPlace rule 5, but score anyway).
        foreach ($placed as $p) {
            $dr        = $p['across'] ? 0 : 1;
            $dc        = $p['across'] ? 1 : 0;
            $hasCross  = false;
            foreach (array_keys($p['normLetters']) as $i) {
                $key = ($p['row'] + $i * $dr) . '_' . ($p['col'] + $i * $dc);
                if (($cellWordCount[$key] ?? 0) >= 2) {
                    $hasCross = true;
                    break;
                }
            }
            if (! $hasCross) {
                $isolated++;
            }
        }

        // Bounding box.
        $keys = array_keys($cells);
        $rows = array_map(fn ($k) => (int) explode('_', $k, 2)[0], $keys);
        $cols = array_map(fn ($k) => (int) explode('_', $k, 2)[1], $keys);
        $area = (max($rows) - min($rows) + 1) * (max($cols) - min($cols) + 1);

        return ($n * 10) + ($crossings * 4) - ($isolated * 20) - (int) ($area / max($n, 1));
    }

    /**
     * Verify that all placed words form a single connected component
     * (every word shares at least one cell with the main cluster).
     *
     * @param  array<int, array<string, mixed>>  $placed
     */
    private function allWordsConnected(array $placed): bool
    {
        $n = count($placed);
        if ($n <= 1) {
            return true;
        }

        // Build a per-word set of occupied cell keys for fast lookup.
        $wordCells = [];
        foreach ($placed as $idx => $p) {
            $dr = $p['across'] ? 0 : 1;
            $dc = $p['across'] ? 1 : 0;
            $wordCells[$idx] = [];
            foreach (array_keys($p['normLetters']) as $i) {
                $wordCells[$idx][($p['row'] + $i * $dr) . '_' . ($p['col'] + $i * $dc)] = true;
            }
        }

        // Two words are adjacent if they share at least one cell.
        $adj = array_fill(0, $n, []);
        for ($a = 0; $a < $n; $a++) {
            for ($b = $a + 1; $b < $n; $b++) {
                if (! empty(array_intersect_key($wordCells[$a], $wordCells[$b]))) {
                    $adj[$a][] = $b;
                    $adj[$b][] = $a;
                }
            }
        }

        // BFS from word 0.
        $visited = [0 => true];
        $queue   = [0];
        while (! empty($queue)) {
            $cur = array_shift($queue);
            foreach ($adj[$cur] as $nb) {
                if (! isset($visited[$nb])) {
                    $visited[$nb] = true;
                    $queue[]      = $nb;
                }
            }
        }

        return count($visited) === $n;
    }

    // ─── Grid serialisation ───────────────────────────────────────────────────

    /**
     * Convert the sparse cells map to a 2-D grid array.
     *
     * @param  array<string, string>  $cells
     * @return array{0: array<int, array<int, string|null>>, 1: int, 2: int}  [grid, minRow, minCol]
     */
    private function cellsToGrid(array $cells): array
    {
        $minR = $minC = PHP_INT_MAX;
        $maxR = $maxC = PHP_INT_MIN;
        foreach (array_keys($cells) as $key) {
            [$r, $c] = explode('_', $key, 2);
            $r = (int) $r;
            $c = (int) $c;
            if ($r < $minR) $minR = $r;
            if ($r > $maxR) $maxR = $r;
            if ($c < $minC) $minC = $c;
            if ($c > $maxC) $maxC = $c;
        }
        $grid = array_fill(0, $maxR - $minR + 1, array_fill(0, $maxC - $minC + 1, null));
        foreach ($cells as $key => $letter) {
            [$r, $c] = explode('_', $key, 2);
            $grid[(int) $r - $minR][(int) $c - $minC] = $letter;
        }
        return [$grid, $minR, $minC];
    }

    // ─── Clue numbering ───────────────────────────────────────────────────────

    /**
     * Assign clue numbers in standard crossword reading order.
     *
     * Words whose start cell is the same position share the same number — e.g.
     * "3 Across" and "3 Down" both begin at the same cell.
     *
     * @param  array<int, array<string, mixed>>  $placed
     * @return array<int, array<string, mixed>>
     */
    private function buildClues(array $placed, int $minR, int $minC): array
    {
        usort($placed, function ($a, $b) {
            if ($a['row'] !== $b['row']) {
                return $a['row'] - $b['row'];
            }
            return $a['col'] - $b['col'];
        });

        $number  = 0;
        $lastKey = null;
        $clues   = [];

        foreach ($placed as $p) {
            $posKey = "{$p['row']}_{$p['col']}";
            if ($posKey !== $lastKey) {
                $number++;
                $lastKey = $posKey;
            }

            $clues[] = [
                'number'    => $number,
                'direction' => $p['across'] ? 'across' : 'down',
                'clue'      => $p['clue'],
                'answer'    => $p['word'],
                'row'       => $p['row'] - $minR,
                'col'       => $p['col'] - $minC,
                'length'    => count($p['letters']),
            ];
        }

        return $clues;
    }

    // ─── Configuration ────────────────────────────────────────────────────────

    /**
     * Return the configured lexical entry group IDs for crossword puzzles.
     * Falls back to LexicalEntryGroup::safe() when no configuration exists.
     *
     * @return array<int>
     */
    private function getLexicalEntryGroupIds(): array
    {
        return Cache::remember('ed.game.crossword.lexical-entry-groups', DateInterval::createFromDateString('1 day'), function () {
            $configured = GameCrosswordLexicalEntryGroup::pluck('lexical_entry_group_id')->toArray();
            if (! empty($configured)) {
                return $configured;
            }

            return LexicalEntryGroup::safe()->pluck('id')->toArray();
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Normalise a raw dictionary word into a plain crossword-safe string.
     *
     * Rules applied in order:
     *  1. Comma-separated variants ("la-, lá") → take the first variant only.
     *  2. Strip parenthetical optional suffixes ("asar(o)" → "asar").
     *  3. Remove all hyphens — prefix ("-sta"), suffix ("car-"), and internal
     *     compounds ("nur-menel" → "nurmenel").
     */
    private function cleanWord(string $word): string
    {
        // 1. Multiple variants separated by comma — keep only the first.
        $word = explode(',', $word)[0];

        // 2. Strip parenthetical content, e.g. "asar(o)" → "asar".
        $word = preg_replace('/\([^)]*\)/u', '', $word);

        // 3. Remove all hyphens.
        $word = str_replace('-', '', $word);

        $word = trim($word);

        // 4. Reject if spaces remain — multi-word phrases cannot be crossword entries.
        if (str_contains($word, ' ')) {
            return '';
        }

        return $word;
    }

    private function normalizeForMatching(string $word): string
    {
        // Use transformAccentsIntoLetters=false so accented vowels map 1-to-1
        // (í → i, not í → ii). This keeps count($normLetters) === count($letters),
        // which is required for end-cap checks and crossing-index arithmetic.
        // It also makes sívë, sivë, síve, and sive all normalize to "sive",
        // so variant spellings can cross each other correctly.
        return StringHelper::transliterate($word, false);
    }

    private function mbSplit(string $s): array
    {
        $out = [];
        $len = mb_strlen($s, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $out[] = mb_substr($s, $i, 1, 'UTF-8');
        }
        return $out;
    }
}
