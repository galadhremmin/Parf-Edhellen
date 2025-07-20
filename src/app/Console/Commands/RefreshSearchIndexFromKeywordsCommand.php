<?php

namespace App\Console\Commands;

use App\Models\LexicalEntry;
use App\Models\Keyword;
use App\Repositories\SearchIndexRepository;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

class RefreshSearchIndexFromKeywordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-search:refresh-from-keywords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes all search indexes using stored keywords.';

    /**
     * Search index repository used to refresh search keywords.
     *
     * @var SearchIndexRepository
     */
    private $_searchIndexRepository;

    /**
     * @var ConsoleOutput
     */
    private $_logger;

    public function __construct(SearchIndexRepository $searchIndexRepository)
    {
        parent::__construct();
        $this->_searchIndexRepository = $searchIndexRepository;
        $this->_logger = new ConsoleOutput;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $noOfSentenceKeywords = $this->refreshSentences();
        $noOfGlossaryKeywords = $this->refreshGlossary();

        $this->info('Glossary keywords: '.$noOfGlossaryKeywords);
        $this->info('Sentence keywords: '.$noOfSentenceKeywords);
    }

    private function refreshGlossary()
    {
        if (! $this->confirm('Do you want to reprocess the glossary?')) {
            return 0;
        }

        $baseQuery = Keyword::whereNull('sentence_fragment_id') //
            ->whereNotNull('gloss_id')
            ->with('keyword_language', 'wordEntity');
        $numberOfKeywords = $baseQuery->count();

        $this->info(sprintf('There are %d keywords to rebuild the index from.', $numberOfKeywords));

        $take = intval($this->ask('How many keywords do you want to process?', 100000));
        $skip = intval($this->ask('How many glosses do you want to skip?', 0));

        if (! $skip) {
            $skip = 0;
        }
        if (! $take) {
            $take = 40000;
        }

        if (! $this->confirm(sprintf('Do you want to skip %d keywords and process %d keywords (final keywords no %d)?', $skip, $take, $skip + $take))) {
            $this->info('Cancelling...');

            return 0;
        }

        $keywords = $baseQuery
            ->skip($skip)
            ->take($skip + $take)
            ->cursor();

        $count = 0;
        $erroneous = [];
        $lexicalEntryCache = [];
        foreach ($keywords as $keyword) {
            $this->_logger->writeln('# '.$keyword->keyword);
            if ($keyword->lexical_entry_id) {
                if (! isset($lexicalEntryCache[$keyword->lexical_entry_id])) {
                    $lexicalEntryCache[$keyword->lexical_entry_id] = LexicalEntry::with('word', 'lexical_entry_group', 'speech', 'lexical_entry_inflections') //
                        ->find($keyword->lexical_entry_id);
                }

                $lexicalEntry = $lexicalEntryCache[$keyword->lexical_entry_id];
                if ($lexicalEntry) {
                    if (! $lexicalEntry->is_deleted) {
                        $this->_searchIndexRepository->createIndex($lexicalEntry, $keyword->wordEntity, $keyword->keyword_language, $keyword->keyword);
                        $this->_logger->writeln('   Refreshed for "'.$keyword->wordEntity->word.'" -> "'.$keyword->keyword.'".');
                        $this->_logger->writeln('   Gloss: '.$keyword->lexical_entry_id);
                        $count += 1;
                    } else {
                        $this->_logger->writeln('   Gloss '.$keyword->lexical_entry_id.' is deleted.');
                    }
                } else {
                    if (! $keyword->sense_id) {
                        $erroneous[] = $keyword->lexical_entry_id;
                    } else {
                        $this->_logger->writeln('   Gloss not found: '.$keyword->lexical_entry_id);

                        // remove the invalid gloss reference from the Keyword
                        $keyword->lexical_entry_id = null;
                        $keyword->save();
                        $count += 1;
                    }
                }
            }
        }

        foreach ($lexicalEntryCache as $id => $lexicalEntry) {
            if (! $lexicalEntry) {
                continue;
            }
            $this->_logger->writeln('# Registering inflections for '.$lexicalEntry->id);
            foreach ($lexicalEntry->lexical_entry_inflections as $inflection) {
                if ($lexicalEntry->word->word !== $inflection->word) {
                    $this->_logger->writeln('   "'.$lexicalEntry->word->word.'" -> "'.$inflection->word.'".');
                    $this->_searchIndexRepository->createIndex($lexicalEntry, $lexicalEntry->word, $inflection->language, $inflection->word);
                }
            }
        }

        if (! empty($erroneous)) {
            $erroneous = array_unique($erroneous);
            $this->warn(sprintf('Discovered %d invalid glosses.', count($erroneous)));
            $delete = $this->ask('Do you want to delete them?');

            if ($delete) {

            }
        }

        return $count;
    }

    private function refreshSentences()
    {
        if (! $this->confirm('Do you want to reprocess all phrases?')) {
            return 0;
        }

        // TODO: Needs to be implemented.

        return 0;
    }
}
