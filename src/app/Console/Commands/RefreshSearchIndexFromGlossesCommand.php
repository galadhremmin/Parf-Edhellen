<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\{
    Gloss,
    Keyword,
    Sense,
};
use App\Models\Initialization\Morphs;
use App\Repositories\{
    KeywordRepository,
    SearchIndexRepository
};

class RefreshSearchIndexFromGlossesCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-search:refresh-from-glosses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes all search indexes using the glossary.';

    /**
     * Search index repository used to refresh search keywords.
     * 
     * @var SearchIndexRepository
     */
    private $_searchIndexRepository;

    /**
     * Search index repository used to refresh search keywords.
     * 
     * @var KeywordRepository
     */
    private $_keywordRepository;

    public function __construct(KeywordRepository $keywordRepository,
        SearchIndexRepository $searchIndexRepository)
    {
        parent::__construct();
        $this->_keywordRepository = $keywordRepository;
        $this->_searchIndexRepository = $searchIndexRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $glosses = Gloss::active()
            ->doesntHave('keywords')
            ->get();
        
        foreach ($glosses as $gloss) {
            $this->_keywordRepository->createKeyword($gloss->word, $gloss->sense, $gloss);
            $this->_searchIndexRepository->createIndex($gloss, $gloss->word);
        }
    }
}
