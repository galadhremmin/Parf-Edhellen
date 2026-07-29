<?php

namespace App\Console\Commands;

use App\Jobs\RebuildLexicalEntryDerivationData;
use Illuminate\Console\Command;

class RebuildLexicalEntryDerivationDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-import:rebuild-derivation-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuilds the precomputed lexical_entry_derivation_data table (derivations, derivatives, phonetic developments) for every entry that qualifies. Normally dispatched automatically at the end of the Eldamo import — use this for a manual/on-demand rebuild.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        RebuildLexicalEntryDerivationData::dispatchSync();
        $this->info('Dispatched RebuildLexicalEntryDerivationData synchronously.');
    }
}
