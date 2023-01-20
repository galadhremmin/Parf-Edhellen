<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\{
    Gloss,
    Keyword,
    Sense,
};
use App\Models\Initialization\Morphs;
use App\Repositories\SearchIndexRepository;

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

    public function __construct(SearchIndexRepository $searchIndexRepository)
    {
        parent::__construct();
        $this->_searchIndexRepository = $searchIndexRepository;
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

        $take = intval( $this->ask('How many keywords do you want to process?', 100000) );
        $skip = intval( $this->ask('How many glosses do you want to skip?', 0) );

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
        foreach ($keywords as $keyword) {
            if ($keyword->gloss_id) {
                $gloss = Gloss::find($keyword->gloss_id);
                if ($gloss) {
                    if (! $gloss->is_deleted) {
                        $this->_searchIndexRepository->createIndex($gloss, $keyword->wordEntity, $keyword->keyword_language, $keyword->keyword);
                    }
                } else {
                    if (! $keyword->sense_id) {
                        $erroneous[] = $keyword->gloss_id;
                    } else {
                        // remove the invalid gloss reference from the Keyword
                        $keyword->gloss_id = NULL;
                        $keyword->save();
                    }
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
