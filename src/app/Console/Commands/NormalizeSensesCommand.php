<?php

namespace App\Console\Commands;

use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseTerm;
use App\Services\Flashcards\VerbSpeechCatalogue;
use App\Services\Senses\NormalizedTerm;
use App\Services\Senses\SenseNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NormalizeSensesCommand extends Command
{
    protected $signature = 'ed-senses:normalize
        {--from=0 : Resume from this sense ID}
        {--batch=500 : Senses per batch}
        {--pause=100 : Milliseconds to wait between batches}';

    protected $description = 'Rebuilds sense_terms for every sense in use. Safe to re-run; --from resumes an interrupted run.';

    public function handle(SenseNormalizer $normalizer, VerbSpeechCatalogue $verbs): int
    {
        $started = microtime(true);
        $senses = 0;
        $terms = 0;

        // a handful of legacy senses lost their word and have nothing to normalise
        Sense::whereHas('lexical_entries', fn ($query) => $query->active())
            ->whereHas('word')
            ->where('id', '>=', (int) $this->option('from'))
            ->with([
                'word:id,word',
                'lexical_entries' => fn ($query) => $query->active()->select('id', 'sense_id', 'speech_id'),
            ])
            ->select('id')
            ->chunkById((int) $this->option('batch'), function (Collection $batch) use ($normalizer, $verbs, &$senses, &$terms) {
                $batchStarted = microtime(true);
                $rows = $batch->flatMap(fn (Sense $sense) => $normalizer
                    ->normalize($sense->word->word, $this->isVerb($sense, $verbs))
                    ->map(fn (NormalizedTerm $term) => [
                        'sense_id' => $sense->id,
                        'position' => $term->position,
                        'term' => $term->term,
                        'term_key' => $term->key,
                        'is_verb' => $term->isVerb,
                    ]));

                DB::transaction(function () use ($batch, $rows) {
                    SenseTerm::whereIn('sense_id', $batch->modelKeys())->delete();
                    SenseTerm::insert($rows->all());
                });

                $senses += $batch->count();
                $terms += $rows->count();
                $this->line(sprintf('[%s] up to sense %d: %d senses, %d terms, last batch %d ms',
                    now()->format('H:i:s'), $batch->last()->id, $senses, $terms, (microtime(true) - $batchStarted) * 1000));

                usleep((int) $this->option('pause') * 1000);
            });

        $this->info(sprintf('Normalised %d senses into %d terms in %.1f s.', $senses, $terms, microtime(true) - $started));

        return self::SUCCESS;
    }

    /**
     * A sense used only by verbs is a verb even when written without "to".
     */
    private function isVerb(Sense $sense, VerbSpeechCatalogue $verbs): bool
    {
        return $sense->lexical_entries->every(fn (LexicalEntry $entry) => $verbs->isVerb($entry->speech_id));
    }
}
