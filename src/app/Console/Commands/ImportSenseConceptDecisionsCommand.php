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
use App\Services\Senses\ConceptApplication;
use App\Services\Senses\ConceptDecision;
use App\Services\Senses\ConceptDecisionApplier;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Applies answers to the questions `ed-senses:export-questions` wrote, whoever answered them. An answer may only
 * name meanings its question offered; anything else goes to an editor rather than into the dictionary.
 */
class ImportSenseConceptDecisionsCommand extends Command
{
    private const CHUNK_SIZE = 200;

    protected $signature = 'ed-senses:import-decisions
        {--directory=sense-backfill : Where under storage/app the questions and answers live}
        {--decided-by=backfill : Who answered, for the audit log}
        {--again : Apply answers for senses this source has already decided}
        {--pause=100 : Milliseconds to wait between batches}
        {--dry-run : Report what would happen, changing nothing}';

    protected $description = 'Applies answered questions to the dictionary, validating every answer against what its question offered. Safe to re-run, and resumable.';

    public function handle(SenseTermRepository $senseTerms, ConceptDecisionApplier $applier, ConceptAuditRepository $audit,
        ConceptRepository $concepts): int
    {
        $directory = storage_path('app/'.$this->option('directory'));
        $offered = $this->offeredBySense($directory);
        if ($offered->isEmpty()) {
            $this->error("No manifest in {$directory}: run ed-senses:export-questions first.");

            return self::FAILURE;
        }

        $decisions = $this->answers($directory);
        if ($decisions->isEmpty()) {
            $this->error("No answers in {$directory}/answers.");

            return self::FAILURE;
        }

        // a sense that already has a concept keeps it; one still waiting is exactly what a later answer is for
        $placed = $this->option('again')
            ? collect()
            : SenseConcept::distinct()->pluck('sense_id')->flip();

        $outcomes = collect();
        $started = microtime(true);

        foreach ($decisions->chunk(self::CHUNK_SIZE) as $batch) {
            $senses = $senseTerms->normalizable()->with('terms')->whereIn('senses.id', $batch->keys())->get()->keyBy('id');

            foreach ($batch as $senseId => $decision) {
                $outcomes->push($this->applyOne($senseId, $decision, $senses->get($senseId),
                    $offered->get($senseId), $placed, $applier, $audit));
            }

            usleep((int) $this->option('pause') * 1000);
        }

        if (! $this->option('dry-run')) {
            $this->line(sprintf('Rebuilt the hierarchy: %d ancestor links.', $concepts->rebuildClosure()));
        }

        $this->newLine();
        $outcomes->countBy()->sortDesc()->each(fn (int $count, string $outcome) => $this->line(sprintf('  %-34s %6d', $outcome, $count)));
        $this->info(sprintf('%s %d answers in %.1f s.',
            $this->option('dry-run') ? 'Checked' : 'Applied', $decisions->count(), microtime(true) - $started));

        return self::SUCCESS;
    }

    /**
     * Applies one answer, or says why it couldn't be.
     *
     * @param  string[]|null  $offered  the meanings its question showed
     * @param  Collection<int, int>  $placed  senses that already have a concept, keyed by ID
     * @return string what happened, for the summary
     */
    private function applyOne(int $senseId, ConceptDecision $decision, ?Sense $sense, ?array $offered, Collection $placed,
        ConceptDecisionApplier $applier, ConceptAuditRepository $audit): string
    {
        if ($offered === null) {
            return 'no question asked about this sense';
        }

        if ($sense === null) {
            return 'sense no longer in use';
        }

        if ($placed->has($senseId)) {
            return 'already has a concept';
        }

        if ($decision->confidence < 0 || $decision->confidence > 100) {
            return 'confidence outside 0-100';
        }

        if ($this->option('dry-run')) {
            return $this->predict($decision, $offered);
        }

        $application = $applier->apply($sense, $decision, ConceptSource::BACKFILL, $offered);
        $audit->record(new ConceptAuditEntry($sense, ConceptSource::BACKFILL, $application,
            $this->option('decided-by'), $offered, $decision));

        return $this->describe($application);
    }

    /**
     * What a real run would make of this answer, by the same two refusals the applier makes.
     *
     * @param  string[]  $offered
     */
    private function predict(ConceptDecision $decision, array $offered): string
    {
        return match (true) {
            array_diff($decision->synsetIds, $offered) !== [] => 'would go to review: '.ConceptReviewReason::INVALID_ANSWER->value,
            $decision->confidence < (int) config('senses.minimum_confidence') => 'would go to review: '.ConceptReviewReason::UNSURE->value,
            default => 'would be applied',
        };
    }

    /**
     * @return string the outcome, with the reason when a sense went to an editor
     */
    private function describe(ConceptApplication $application): string
    {
        return $application->reviewReason === null
            ? $application->outcome->value
            : $application->outcome->value.': '.$application->reviewReason->value;
    }

    /**
     * The meanings each sense's question offered, by sense ID.
     *
     * @return Collection<int, string[]>
     */
    private function offeredBySense(string $directory): Collection
    {
        $manifest = $this->readJson($directory.'/manifest.json');
        $offered = collect();

        foreach ($manifest ?? [] as $group) {
            foreach ($group['sense_ids'] as $senseId) {
                $offered[$senseId] = $group['candidate_synset_ids'];
            }
        }

        return $offered;
    }

    /**
     * Every answer, by sense ID. A later file wins, so a re-answered question can be corrected by answering it again.
     *
     * @return Collection<int, ConceptDecision>
     */
    private function answers(string $directory): Collection
    {
        $answers = collect();

        foreach (File::glob($directory.'/answers/*.json') as $path) {
            $decisions = $this->readJson($path)['decisions'] ?? null;
            if (! is_array($decisions)) {
                $this->warn(sprintf('%s has no decisions: skipped.', basename($path)));

                continue;
            }

            foreach ($decisions as $answer) {
                if (isset($answer['sense_id'])) {
                    $answers[(int) $answer['sense_id']] = ConceptDecision::fromAnswer($answer);
                }
            }
        }

        return $answers;
    }

    /**
     * @return array<mixed>|null null when the file is missing or isn't JSON
     */
    private function readJson(string $path): ?array
    {
        if (! File::exists($path)) {
            return null;
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : null;
    }
}
