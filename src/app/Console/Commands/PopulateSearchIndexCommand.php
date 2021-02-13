<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use App\Helpers\StringHelper;
use App\Models\{
    Gloss,
    Keyword,
    SearchKeyword
};

class PopulateSearchIndexCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-search:refresh-gloss-sentence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes all search indexes for glossary and sentences.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SearchKeyword::whereIn('search_group', [
            SearchKeyword::SEARCH_GROUP_DICTIONARY,
            SearchKeyword::SEARCH_GROUP_SENTENCE
        ])->delete();

        foreach (Keyword::cursor() as $keyword) {
            $entities = [];
            $searchGroup = SearchKeyword::SEARCH_GROUP_UNASSIGNED;
            $glossGroupId = null;
            $speechId = null;
            $languageId = null;

            if ($keyword->gloss_id) {
                $gloss = Gloss::find($keyword->gloss_id);
                if ($gloss) {
                    $entities['gloss'] = $keyword->gloss_id;
                    $searchGroup = SearchKeyword::SEARCH_GROUP_DICTIONARY;
                    $glossGroupId = $gloss->gloss_group_id;
                    $speechId     = $gloss->speech_id;
                    $languageId   = $gloss->language_id;
                } else {
                    $this->info(sprintf('Failed to find a gloss with ID: %d.', $keyword->gloss_id));
                }
            }

            if ($keyword->sense_id) {
                $entities['sense'] = $keyword->sense_id;
                $searchGroup = SearchKeyword::SEARCH_GROUP_DICTIONARY;
            }
            if ($keyword->sentence_fragment_id) {
                $entities['fragment'] = $keyword->sentence_fragment_id;
                $searchGroup = SearchKeyword::SEARCH_GROUP_SENTENCE;
            }

            $normalizedKeyword           = StringHelper::normalize($keyword->keyword, true);
            $normalizedKeywordUnaccented = StringHelper::normalize($keyword->keyword, false);

            $normalizedKeywordReversed           = strrev($normalizedKeyword);
            $normalizedKeywordUnaccentedReversed = strrev($normalizedKeywordUnaccented);

            foreach ($entities as $entityName => $entityId) {
                $data = [
                    'search_group'                           => $searchGroup,
                    'keyword'                                => $keyword->keyword,
                    'normalized_keyword'                     => $normalizedKeyword,
                    'normalized_keyword_unaccented'          => $normalizedKeywordUnaccented,
                    'normalized_keyword_reversed'            => $normalizedKeywordReversed,
                    'normalized_keyword_reversed_unaccented' => $normalizedKeywordUnaccentedReversed,
                    'keyword_length'                         => mb_strlen($keyword->keyword),
                    'normalized_keyword_length'              => mb_strlen($normalizedKeyword),
                    'normalized_keyword_unaccented_length'   => mb_strlen($normalizedKeywordUnaccented),
                    'normalized_keyword_reversed_length'     => mb_strlen($normalizedKeywordUnaccented),
                    'normalized_keyword_reversed_unaccented_length' => mb_strlen($normalizedKeywordUnaccentedReversed),
                    'entity_name'    => $entityName,
                    'entity_id'      => $entityId,
                    'is_old'         => $keyword->is_old,
                    'word'           => empty($keyword->word) ? $keyword->keyword : $keyword->word,
                    'word_id'        => $keyword->word_id,
                    'gloss_group_id' => $glossGroupId,
                    'language_id'    => $languageId,
                    'speech_id'      => $speechId
                ];
                SearchKeyword::create($data);
            }

            $this->info(sprintf('%d: Done', $keyword->id));
        }
    }
}
