<?php

namespace App\Console\Commands;

use App\Models\Sense;
use App\Repositories\ConceptRepository;
use App\Repositories\SenseTermRepository;
use App\Services\Senses\SenseConceptResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AssignConceptsByRuleCommand extends Command
{
    protected $signature = 'ed-senses:assign-by-rule
        {--again : Decide senses that have been decided before, adding a fresh entry to the audit log}
        {--reconsider : Decide again only the senses left with no concept and nobody waiting to review them}
        {--from=0 : Resume from this sense ID}
        {--batch=500 : Senses per batch}
        {--pause=100 : Milliseconds to wait between batches}';

    protected $description = 'Gives a concept to every sense the rules can place on their own, and leaves the rest for a judge or an editor. Asks no model, so it is safe to run over the whole dictionary; re-running it changes nothing else.';

    public function handle(SenseTermRepository $senseTerms, SenseConceptResolver $resolver, ConceptRepository $concepts): int
    {
        $started = microtime(true);
        $outcomes = collect();

        $senseTerms->normalizable()
            ->with('terms')
            ->whereHas('terms')
            ->whereDoesntHave('concepts')
            // senses nobody is waiting to review: what a rule change can settle without asking again
            ->when($this->option('reconsider'), fn ($query) => $query->whereDoesntHave('concept_review'))
            // a sense already decided stays decided, so the log doesn't fill with the same answer twice
            ->when(! $this->option('again') && ! $this->option('reconsider'),
                fn ($query) => $query->whereDoesntHave('concept_decisions'))
            ->where('id', '>=', (int) $this->option('from'))
            ->chunkById((int) $this->option('batch'), function (Collection $batch) use ($resolver, $outcomes) {
                $batchStarted = microtime(true);
                $batch->each(fn (Sense $sense) => $outcomes->push(
                    $resolver->resolve($sense, mayAsk: false, mayQueue: ! $this->option('reconsider'))->value
                ));

                $this->line(sprintf('[%s] up to sense %d: %d senses, last batch %d ms',
                    now()->format('H:i:s'), $batch->last()->id, $outcomes->count(), (microtime(true) - $batchStarted) * 1000));

                usleep((int) $this->option('pause') * 1000);
            });

        $closure = $concepts->rebuildClosure();

        $this->newLine();
        $outcomes->countBy()->sortDesc()->each(fn (int $count, string $outcome) => $this->line(sprintf('  %-32s %6d', $outcome, $count)));
        $this->info(sprintf('Rebuilt the hierarchy: %d ancestor links. Done in %.1f s.', $closure, microtime(true) - $started));

        return self::SUCCESS;
    }
}
