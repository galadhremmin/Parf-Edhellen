<?php

namespace App\Console\Commands;

use App\Models\WordNetException;
use App\Models\WordNetSense;
use App\Models\WordNetSynset;
use App\Services\WordNet\WordNetDictionaryReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use PharData;

/**
 * WordNet 3.1 Copyright 2011 by Princeton University. All rights reserved. The data is downloaded on import
 * and not redistributed with this project; see https://wordnet.princeton.edu/license-and-commercial-use.
 */
class ImportWordNetCommand extends Command
{
    private const SOURCE_URL = 'https://wordnetcode.princeton.edu/wn3.1.dict.tar.gz';

    private const CHUNK_SIZE = 1000;

    protected $signature = 'ed-import:wordnet
        {--path= : A WordNet `dict` directory; downloads WordNet 3.1 when omitted}
        {--pause=100 : Milliseconds to wait between batches}';

    protected $description = 'Replaces the wordnet_* tables with Princeton WordNet 3.1. Safe to re-run: each table is emptied first.';

    public function handle(): int
    {
        $path = $this->option('path') ?: $this->download();
        $reader = new WordNetDictionaryReader($path);
        $pause = (int) $this->option('pause');

        $this->load('wordnet_synsets', fn () => $reader->synsets(), $pause);
        $this->load('wordnet_senses', fn () => $reader->senses(), $pause);
        $this->load('wordnet_hypernyms', fn () => $reader->hypernyms(), $pause);
        $this->load('wordnet_exceptions', fn () => $reader->exceptions(), $pause);

        $this->info(sprintf('Imported %d synsets, %d senses and %d exceptions.',
            WordNetSynset::count(), WordNetSense::count(), WordNetException::count()));

        return self::SUCCESS;
    }

    /**
     * Bulk inserts through the query builder: ~420k rows of reference data don't need model events.
     */
    private function load(string $table, callable $rows, int $pause): void
    {
        DB::table($table)->truncate();
        $started = microtime(true);
        $total = 0;

        LazyCollection::make($rows)
            ->chunk(self::CHUNK_SIZE)
            ->each(function (LazyCollection $chunk, int $batch) use ($table, $pause, &$total) {
                $batchStarted = microtime(true);
                DB::table($table)->insert($chunk->values()->all());
                $total += $chunk->count();

                if ($batch % 50 === 0) {
                    $this->line(sprintf('[%s] %s: %d rows, last batch %d ms',
                        now()->format('H:i:s'), $table, $total, (microtime(true) - $batchStarted) * 1000));
                }

                usleep($pause * 1000);
            });

        $this->info(sprintf('[%s] %s: %d rows in %.1f s', now()->format('H:i:s'), $table, $total, microtime(true) - $started));
    }

    /**
     * Downloads and unpacks WordNet into storage once; later runs reuse the download.
     */
    private function download(): string
    {
        $directory = storage_path('app/wordnet');
        $dict = $directory.'/dict';
        if (File::isDirectory($dict)) {
            $this->line("Using the WordNet download in {$dict}.");

            return $dict;
        }

        File::ensureDirectoryExists($directory);
        $archive = $directory.'/wn3.1.dict.tar.gz';

        $this->line('Downloading '.self::SOURCE_URL.'…');
        Http::timeout(120)->sink($archive)->get(self::SOURCE_URL)->throw();
        (new PharData($archive))->extractTo($directory, null, true);

        return $dict;
    }
}
