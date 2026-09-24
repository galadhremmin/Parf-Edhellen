<?php

namespace App\Console\Commands;

use App\Models\Sense;
use App\Models\SenseConcept;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\SenseTermRepository;
use App\Repositories\ValueObjects\ConceptAuditEntry;
use App\Services\Enumerations\ConceptOutcome;
use App\Services\Senses\ConceptApplication;
use App\Services\Senses\ConceptDecision;
use App\Services\Senses\ConceptDecisionApplier;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Brings a copy of the dictionary to the same state as the one the decisions were made on: the same concepts, the
 * same senses waiting for an editor, and the same audit trail. Nobody is asked to judge anything again, and the
 * thresholds that applied at the time are honoured rather than re-applied.
 */
class ReplaySenseConceptDecisionsCommand extends Command
{
    private const CHUNK_SIZE = 500;

    protected $signature = 'ed-senses:replay-decisions
        {--file=sense-concept-decisions.jsonl : The exported decisions, under storage/app}
        {--pause=100 : Milliseconds to wait between batches}
        {--dry-run : Report what would happen, changing nothing}';

    protected $description = 'Replays exported decisions onto this dictionary, so another copy reaches the same state without judging anything again.';

    public function handle(SenseTermRepository $senseTerms, ConceptDecisionApplier $applier, ConceptRepository $concepts,
        ConceptAuditRepository $audit): int
    {
        $path = storage_path('app/'.$this->option('file'));
        if (! File::exists($path)) {
            $this->error("No such file: {$path}");

            return self::FAILURE;
        }

        $started = microtime(true);
        $outcomes = collect();
        $placed = SenseConcept::distinct()->pluck('sense_id')->flip();

        foreach ($this->decisions($path)->chunk(self::CHUNK_SIZE) as $batch) {
            $senses = $senseTerms->normalizable()->with('terms')->whereIn('senses.id', $batch->pluck('sense_id'))->get()->keyBy('id');

            foreach ($batch as $line) {
                $outcomes->push($this->replay($line, $senses->get($line['sense_id']), $placed, $applier, $concepts, $audit));
            }

            usleep((int) $this->option('pause') * 1000);
        }

        if (! $this->option('dry-run')) {
            $this->line(sprintf('Rebuilt the hierarchy: %d ancestor links.', $concepts->rebuildClosure()));
        }

        $this->newLine();
        $outcomes->countBy()->sortDesc()->each(fn (int $count, string $outcome) => $this->line(sprintf('  %-34s %6d', $outcome, $count)));
        $this->info(sprintf('%s %d decisions in %.1f s.',
            $this->option('dry-run') ? 'Checked' : 'Replayed', $outcomes->count(), microtime(true) - $started));

        return self::SUCCESS;
    }

    /**
     * Replays one decision exactly as it was made: the concepts it assigned, or the reason it is waiting.
     *
     * @param  array<string, mixed>  $line
     * @param  Collection<int, int>  $placed  senses that already have a concept, keyed by ID
     * @return string what happened, for the summary
     */
    private function replay(array $line, ?Sense $sense, Collection $placed, ConceptDecisionApplier $applier,
        ConceptRepository $concepts, ConceptAuditRepository $audit): string
    {
        if ($sense === null) {
            return 'sense not in use here';
        }

        if ($placed->has($sense->id)) {
            return 'already has a concept';
        }

        if ($this->option('dry-run')) {
            return 'would replay: '.$line['outcome'];
        }

        $source = ConceptSource::from($line['source']);
        $decision = ConceptDecision::fromAnswer($line);

        $application = match ($line['outcome']) {
            // the decision was vetted when it was made, so the threshold of the day is not applied again
            ConceptOutcome::ASSIGNED->value => $applier->apply($sense, $decision, $source, $line['candidate_synset_ids'], 0),
            ConceptOutcome::NEEDS_REVIEW->value => $this->review($sense, $line, $concepts),
            default => $this->notAConcept($sense, $concepts),
        };

        $audit->record(new ConceptAuditEntry($sense, $source, $application, $line['decided_by'],
            $line['candidate_synset_ids'], $decision));

        return $application->reviewReason === null
            ? $application->outcome->value
            : $application->outcome->value.': '.$application->reviewReason->value;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function review(Sense $sense, array $line, ConceptRepository $concepts): ConceptApplication
    {
        $reason = ConceptReviewReason::from($line['review_reason'] ?? ConceptReviewReason::NOT_JUDGED->value);
        $concepts->review($sense->id, $reason, null, $line['confidence']);

        return ConceptApplication::review($reason);
    }

    private function notAConcept(Sense $sense, ConceptRepository $concepts): ConceptApplication
    {
        $concepts->clearReview($sense->id);

        return ConceptApplication::of(ConceptOutcome::NOT_A_CONCEPT);
    }

    /**
     * The decisions in the file, one per line.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function decisions(string $path): Collection
    {
        $decisions = collect();
        $handle = fopen($path, 'r');

        while (($line = fgets($handle)) !== false) {
            $decoded = json_decode(trim($line), true);
            if (is_array($decoded) && isset($decoded['sense_id'])) {
                $decisions->push($decoded);
            }
        }

        fclose($handle);

        return $decisions;
    }
}
