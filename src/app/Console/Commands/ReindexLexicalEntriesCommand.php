<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSentenceReindex;
use App\Models\LexicalEntry;
use App\Models\Sentence;
use App\Services\SearchIndexAuditor;
use App\Subscribers\GlossIndexerSubscriber;
use Illuminate\Console\Command;

class ReindexLexicalEntriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-search:reindex
        {--unindexed : Only entries with no search index at all}
        {--incomplete : Only entries whose index is missing terms}
        {--phrases : Only phrases whose index is missing or incomplete}
        {--dry-run : Report what would be reindexed without queueing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queues search reindexing for lexical entries and phrases whose search index is missing or incomplete.';

    public function handle(SearchIndexAuditor $auditor, GlossIndexerSubscriber $indexer): void
    {
        // Neither flag means both, which is what a routine repair run wants.
        $all = ! $this->option('unindexed') && ! $this->option('incomplete') && ! $this->option('phrases');
        $ids = collect();
        $phraseIds = collect();

        if ($all || $this->option('unindexed')) {
            $ids = $auditor->entriesWithoutIndex();
            $this->line(sprintf('%d active entries have no search index.', $ids->count()));
        }

        if ($all || $this->option('incomplete')) {
            $incomplete = collect();
            $terms = 0;
            $auditor->eachEntryWithMissingTerms(function ($id, $missing) use ($incomplete, &$terms) {
                $incomplete->add($id);
                $terms += $missing->count();
            });

            $this->line(sprintf('%d entries are missing %d terms.', $incomplete->count(), $terms));
            $ids = $ids->merge($incomplete)->unique();
        }

        if ($all || $this->option('phrases')) {
            $phraseIds = $auditor->phrasesNeedingReindex();
            $this->line(sprintf('%d phrases need reindexing.', $phraseIds->count()));
        }

        if ($this->option('dry-run')) {
            return;
        }

        Sentence::whereIn('id', $phraseIds)
            ->orderBy('id')
            ->each(function ($sentence) {
                ProcessSentenceReindex::dispatch($sentence)->onQueue('indexing');
                $this->line(sprintf('phrase %d %s', $sentence->id, $sentence->name));
            });

        LexicalEntry::whereIn('id', $ids)
            ->with('word')
            ->orderBy('id')
            ->each(function ($lexicalEntry) use ($indexer) {
                $indexer->reindex($lexicalEntry);
                $this->line(sprintf('%d %s', $lexicalEntry->id, $lexicalEntry->word->word));
            });

        $this->info(sprintf('Dispatched indexing for %d entries and %d phrases to the "indexing" queue.', $ids->count(), $phraseIds->count()));
    }
}
