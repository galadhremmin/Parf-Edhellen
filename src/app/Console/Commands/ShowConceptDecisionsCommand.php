<?php

namespace App\Console\Commands;

use App\Models\SenseConceptDecision;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\Enumerations\ConceptSource;
use Illuminate\Console\Command;

class ShowConceptDecisionsCommand extends Command
{
    protected $signature = 'ed-senses:decisions
        {--sense= : Everything ever decided about one sense}
        {--source= : Only decisions by rule, backfill, gemini or editor}
        {--limit=20 : Decisions to show}';

    protected $description = 'Prints the audit log of what was decided about senses\' meanings: who decided, what was on offer, what they answered and what came of it.';

    public function handle(ConceptAuditRepository $audit): int
    {
        $source = $this->option('source') === null ? null : ConceptSource::from($this->option('source'));

        $decisions = $this->option('sense') !== null
            ? $audit->forSense((int) $this->option('sense'))
            : $audit->recent((int) $this->option('limit'), $source);

        if ($decisions->isEmpty()) {
            $this->warn('Nothing has been decided yet.');

            return self::FAILURE;
        }

        $decisions->each(function (SenseConceptDecision $decision) {
            $this->line(sprintf('<info>%s</info> sense %d "%s"', $decision->created_at->format('Y-m-d H:i'), $decision->sense_id, $decision->sense));
            $this->line(sprintf('  %s → %s: %s', $decision->source->value, $decision->outcome->value, $decision->decided_by));
            $this->line(sprintf('  concepts: %s', collect($decision->concepts)->map(fn (array $concept) => $concept['label'])->implode(', ') ?: '—'));
            $this->line(sprintf('  answered: %s%s%s  confidence %s',
                implode(', ', $decision->synset_ids ?? []) ?: '—',
                $decision->better_word === null ? '' : sprintf(' (%s "%s")', $decision->relation, $decision->better_word),
                $decision->review_reason === null ? '' : ' → '.$decision->review_reason->value,
                $decision->confidence ?? '—'));
            $this->line(sprintf('  on offer: %s', implode(', ', $decision->candidate_synset_ids ?? []) ?: '—'));
        });

        return self::SUCCESS;
    }
}
