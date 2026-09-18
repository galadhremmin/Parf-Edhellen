<?php

namespace App\Console\Commands;

use App\Services\SearchIndexAuditor;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Read-only. Reports active lexical entries whose search index is missing terms the indexer would write today,
 * grouped by where the term comes from, so phrase-derived gaps can be told apart from real ones.
 */
class AuditSearchIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-search:audit-index {--samples=5 : Number of example gaps to print per category}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reports lexical entries whose search index is missing keywords, glosses or inflections.';

    public function handle(SearchIndexAuditor $auditor): void
    {
        // Every gap, as "#<entry id> <term>" strings grouped by the kind of term that's missing.
        $gaps = collect();
        $entriesByKind = collect();

        $totals = $auditor->eachEntryWithMissingTerms(function ($id, $missing) use ($gaps, $entriesByKind) {
            foreach ($missing as $term => $kind) {
                $gaps->put($kind->value, $gaps->get($kind->value, collect())->push(sprintf('#%d "%s"', $id, $term)));
                $entriesByKind->put($kind->value, $entriesByKind->get($kind->value, collect())->add($id));
            }
        });

        $this->line(sprintf('%d active entries have an index; %d have none.', $totals->checked, $totals->unindexed));

        if ($gaps->isEmpty()) {
            $this->info('Every indexed entry contains all of its search terms.');

            return;
        }

        $this->table(['Missing term', 'Terms', 'Entries'], $gaps->map(fn (Collection $terms, string $kind) => [
            $kind, $terms->count(), $entriesByKind->get($kind)->unique()->count(),
        ])->values());

        $gaps->each(fn (Collection $terms, string $kind) => $this->line(
            $kind.': '.$terms->take(intval($this->option('samples')))->implode(', ')
        ));
    }
}
