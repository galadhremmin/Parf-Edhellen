<?php

namespace App\Console\Commands;

use App\Models\Initialization\Morphs;
use App\Models\LexicalEntry;
use App\Models\SearchKeyword;
use App\Subscribers\GlossIndexerSubscriber;
use Illuminate\Console\Command;

class ReindexUnindexedLexicalEntriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-search:reindex-unindexed {--dry-run : List the entries without dispatching indexing jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queues search reindexing for active lexical entries that have no search keywords.';

    public function handle(GlossIndexerSubscriber $indexer): void
    {
        $entityName = Morphs::getAlias(LexicalEntry::class);
        $query = LexicalEntry::active()
            ->whereNotExists(function ($q) use ($entityName) {
                $q->selectRaw('1')
                    ->from((new SearchKeyword)->getTable())
                    ->where('entity_name', $entityName)
                    ->whereColumn('entity_id', 'lexical_entries.id');
            })
            ->with('word');

        $count = $query->count();
        $this->line(sprintf('%d active lexical entries have no search index.', $count));

        if ($count === 0 || $this->option('dry-run')) {
            return;
        }

        foreach ($query->lazyById() as $lexicalEntry) {
            $indexer->reindex($lexicalEntry);
            $this->line(sprintf('%d %s', $lexicalEntry->id, $lexicalEntry->word->word));
        }

        $this->info(sprintf('Dispatched indexing for %d entries to the "indexing" queue.', $count));
    }
}
