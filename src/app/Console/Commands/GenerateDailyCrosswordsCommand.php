<?php

namespace App\Console\Commands;

use App\Models\CrosswordPuzzle;
use App\Services\CrosswordPuzzleGenerator;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyCrosswordsCommand extends Command
{
    protected $signature = 'ed:generate-daily-crosswords
        {--date= : Target date (Y-m-d). Default: today}
        {--language= : Only generate for this language ID (optional)}
        {--words=8 : Number of target words per puzzle}';

    protected $description = 'Generate daily crossword puzzles for all enabled languages (or one language). Skips dates that already have a puzzle.';

    public function handle(CrosswordPuzzleGenerator $generator): int
    {
        $dateStr = $this->option('date');
        $targetDate = $dateStr
            ? Carbon::parse($dateStr)->startOfDay()
            : Carbon::now()->startOfDay();

        $targetWords = (int) $this->option('words');

        $languageId = $this->option('language');
        if ($languageId !== null) {
            $languageId = (int) $languageId;
            $this->info("Generating crossword for language {$languageId} on {$targetDate->toDateString()}...");
            $puzzle = $generator->generateForLanguageAndDate($languageId, $targetDate, $targetWords);
            if ($puzzle !== null) {
                $this->info("Created puzzle ID {$puzzle->id}.");
                return Command::SUCCESS;
            }
            $this->warn('No puzzle created (already exists or generation failed).');
            return Command::SUCCESS;
        }

        $this->info("Generating daily crosswords for {$targetDate->toDateString()}...");
        $languages = \App\Models\GameCrosswordLanguage::pluck('language_id');
        $created = 0;
        $skipped = 0;
        $failed  = 0;
        foreach ($languages as $langId) {
            $langId = (int) $langId;

            // Skip dates that already have a puzzle.
            if (CrosswordPuzzle::query()->where('language_id', $langId)->where('puzzle_date', $targetDate->toDateString())->exists()) {
                $skipped++;
                $this->line("  Language {$langId}: already exists, skipped.");
                continue;
            }

            // Fetch pairs first so we can report a specific reason on failure.
            $pairs = $generator->fetchWordCluePairs($langId, $targetWords);
            if (empty($pairs)) {
                $failed++;
                $this->warn("  Language {$langId}: no word/clue pairs found in the dictionary for this language.");
                continue;
            }

            $this->line("  Language {$langId}: " . count($pairs) . " pairs available, building grid...");
            $puzzle = $generator->generateForLanguageAndDate($langId, $targetDate, $targetWords, $pairs);
            if ($puzzle !== null) {
                $created++;
                $this->info("  Language {$langId}: created puzzle ID {$puzzle->id}.");
            } else {
                $failed++;
                $this->warn("  Language {$langId}: grid placement failed after all attempts (stochastic — try re-running).");
            }
        }
        $this->info("Done. Created: {$created}, skipped (already exists): {$skipped}, failed: {$failed}.");
        return Command::SUCCESS;
    }
}
