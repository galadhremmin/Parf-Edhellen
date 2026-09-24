<?php

namespace App\Console\Commands;

use App\Models\SenseConceptDecision;
use App\Repositories\Enumerations\ConceptSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Writes every judged decision to one file, so another copy of the dictionary can be brought to the same state
 * without asking anybody again. What the rules decide is left out: it is reproducible by running them.
 */
class ExportSenseConceptDecisionsCommand extends Command
{
    private const CHUNK_SIZE = 1000;

    protected $signature = 'ed-senses:export-decisions
        {--out=sense-concept-decisions.jsonl : Where under storage/app to write}';

    protected $description = 'Exports every judged decision about senses\' meanings as one file, for replaying on another copy of the dictionary.';

    public function handle(): int
    {
        $path = storage_path('app/'.$this->option('out'));
        File::ensureDirectoryExists(dirname($path));
        $handle = fopen($path, 'w');
        $written = 0;

        SenseConceptDecision::where('source', '!=', ConceptSource::RULE)
            ->orderBy('id')
            ->chunk(self::CHUNK_SIZE, function ($decisions) use ($handle, &$written) {
                foreach ($decisions as $decision) {
                    fwrite($handle, json_encode([
                        'sense_id' => $decision->sense_id,
                        'sense' => $decision->sense,
                        'source' => $decision->source->value,
                        'decided_by' => $decision->decided_by,
                        'outcome' => $decision->outcome->value,
                        'review_reason' => $decision->review_reason?->value,
                        'confidence' => $decision->confidence,
                        'synset_ids' => $decision->synset_ids ?? [],
                        'better_word' => $decision->better_word,
                        'relation' => $decision->relation,
                        'candidate_synset_ids' => $decision->candidate_synset_ids ?? [],
                    ], JSON_UNESCAPED_UNICODE)."\n");
                    $written++;
                }
            });

        fclose($handle);

        $this->info(sprintf('Wrote %d decisions to %s (%d KB).', $written, $path, File::size($path) / 1024));

        return self::SUCCESS;
    }
}
