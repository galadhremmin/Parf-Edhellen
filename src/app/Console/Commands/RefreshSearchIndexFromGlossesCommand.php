<?php

namespace App\Console\Commands;

use App\Interfaces\ISystemLanguageFactory;
use App\Models\Gloss;
use App\Models\Initialization\Morphs;
use App\Models\Language;
use App\Models\SearchKeyword;
use App\Repositories\KeywordRepository;
use App\Repositories\SearchIndexRepository;
use App\Repositories\WordRepository;
use Illuminate\Console\Command;

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

    /**
     * Search index repository used to refresh search keywords.
     *
     * @var WordRepository
     */
    private $_wordRepository;

    /**
     * English language (default systems language)
     */
    private $_systemLanguage;

    public function __construct(KeywordRepository $keywordRepository,
        SearchIndexRepository $searchIndexRepository,
        WordRepository $wordRepository,
        ISystemLanguageFactory $systemLanguageFactory)
    {
        parent::__construct();
        $this->_keywordRepository = $keywordRepository;
        $this->_searchIndexRepository = $searchIndexRepository;
        $this->_wordRepository = $wordRepository;
        $this->_systemLanguage = $systemLanguageFactory->language();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $unreferencedKeywords = SearchKeyword::where('entity_name', Morphs::getAlias(Gloss::class))
            ->doesntHave('entity');
        $noUnreferencedKeywords = $unreferencedKeywords->count();

        if ($noUnreferencedKeywords > 0) {
            if ($this->confirm(sprintf('There are %d unreferenced keywords. Do you want to delete them?', $noUnreferencedKeywords))) {
                $unreferencedKeywords->delete();
            }
        }

        $deletedGlosses = SearchKeyword::where('entity_name', Morphs::getAlias(Gloss::class))
            ->join('glosses', 'glosses.id', 'entity_id')
            ->where('glosses.is_deleted', 1);
        $noDeletedGlosses = $deletedGlosses->count();

        if ($noDeletedGlosses > 0) {
            if ($this->confirm(sprintf('There are %d deleted glosses still in the search index. Do you want to delete them?', $noDeletedGlosses))) {
                $deletedGlosses->delete();
            }
        }

        $glosses = Gloss::active()->with('translations', 'word', 'sense', 'language');
        $noOfGlosses = $glosses->count();

        if ($this->confirm(sprintf('This operation will refresh %d glosses. Do you want to proceed?', $noOfGlosses))) {
            $i = 0;
            foreach ($glosses->cursor() as $gloss) {
                $this->_keywordRepository->createKeyword($gloss->word, $gloss->sense, $gloss, $gloss->language);
                $this->_searchIndexRepository->createIndex($gloss, $gloss->word, $gloss->language);

                foreach ($gloss->translations as $translation) {
                    $translationWord = $this->_wordRepository->save($translation->translation, $gloss->account_id);
                    $this->_keywordRepository->createKeyword($translationWord, $gloss->sense, $gloss, $this->_systemLanguage);
                    $this->_searchIndexRepository->createIndex($gloss, $translationWord, $this->_systemLanguage);
                }

                echo (++$i).' ('.round(($i / $noOfGlosses) * 100, 2)."%): $gloss->id\n";
            }
        }
    }
}
