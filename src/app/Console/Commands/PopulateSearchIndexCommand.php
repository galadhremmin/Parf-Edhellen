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
    SearchKeyword,
    Sense,
    SentenceFragment
};
use App\Models\Initialization\Morphs;

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
        $noOfSentenceKeywords = $this->refreshSentences();
        $noOfGlossaryKeywords = $this->refreshGlossary();

        $this->info('Glossary keywords: '.$noOfGlossaryKeywords);
        $this->info('Sentence keywords: '.$noOfSentenceKeywords);
    }

    private function refreshGlossary()
    {
        if (! $this->confirm('Do you want to reprocess the glossary? [yes/no]')) {
            return 0;
        }

        $skip = intval( $this->ask('How many glosses do you want to skip?') );
        $take = intval( $this->ask('How many glosses do you want to process?') );

        if (! $skip) {
            $skip = 0;
        }
        if (! $take) {
            $take = 40000;
        }

        if (! $this->confirm(sprintf('Do you want to skip %d glosses and process %d glosses? [yes/no]', $skip, $take))) {
            $this->info('Cancelling...');
            return 0;
        }

        $keywords = Keyword::whereNull('sentence_fragment_id') //
            ->skip($skip)
            ->take($take)
            ->cursor();

        $count = 0;
        foreach ($keywords as $keyword) {
            $entities = [];
            $searchGroup = SearchKeyword::SEARCH_GROUP_UNASSIGNED;
            $glossGroupId = null;
            $speechId = null;
            $languageId = null;

            if ($keyword->gloss_id) {
                $gloss = Gloss::find($keyword->gloss_id);
                if ($gloss) {
                    $entities[Morphs::getAlias(Gloss::class)] = $keyword->gloss_id;
                    $glossGroupId = $gloss->gloss_group_id;
                    $speechId     = $gloss->speech_id;
                    $languageId   = $gloss->language_id;
                } else {
                    $this->info(sprintf('Failed to find a gloss with ID: %d.', $keyword->gloss_id));
                }
            }

            if ($keyword->sense_id) {
                $entities[Morphs::getAlias(Sense::class)] = $keyword->sense_id;
            }

            $normalizedKeyword           = StringHelper::normalize($keyword->keyword, true);
            $normalizedKeywordUnaccented = StringHelper::normalize($keyword->keyword, false);

            $normalizedKeywordReversed           = strrev($normalizedKeyword);
            $normalizedKeywordUnaccentedReversed = strrev($normalizedKeywordUnaccented);

            foreach ($entities as $entityName => $entityId) {
                $word = StringHelper::toLower(StringHelper::clean(empty($keyword->word) ? $keyword->keyword : $keyword->word));
                $keywordString = StringHelper::toLower(StringHelper::clean($keyword->keyword));
                $qualifierData = [
                    'search_group'   => SearchKeyword::SEARCH_GROUP_DICTIONARY,
                    'keyword'        => $keywordString,
                    'entity_name'    => $entityName,
                    'entity_id'      => $entityId,
                    'word'           => $word,
                    'word_id'        => $keyword->word_id,
                ];
                $data = $qualifierData + [
                    'normalized_keyword'                     => $normalizedKeyword,
                    'normalized_keyword_unaccented'          => $normalizedKeywordUnaccented,
                    'normalized_keyword_reversed'            => $normalizedKeywordReversed,
                    'normalized_keyword_reversed_unaccented' => $normalizedKeywordUnaccentedReversed,
                    'keyword_length'                         => mb_strlen($keywordString),
                    'normalized_keyword_length'              => mb_strlen($normalizedKeyword),
                    'normalized_keyword_unaccented_length'   => mb_strlen($normalizedKeywordUnaccented),
                    'normalized_keyword_reversed_length'     => mb_strlen($normalizedKeywordUnaccented),
                    'normalized_keyword_reversed_unaccented_length' => mb_strlen($normalizedKeywordUnaccentedReversed),
                    'is_old'         => $keyword->is_old,
                    'gloss_group_id' => $glossGroupId,
                    'language_id'    => $languageId,
                    'speech_id'      => $speechId
                ];
                SearchKeyword::create($data);
                unset($data);
                unset($qualifierData);
                $count += 1;
            }

            $this->info(sprintf('%d (glossary): %s done', $entityId, $keywordString));
            unset($entities);
        }
        return $count;
    }

    private function refreshSentences()
    {
        if (! $this->confirm('Do you want to reprocess all phrases? [yes/no]')) {
            return 0;
        }

        SearchKeyword::where('search_group', SearchKeyword::SEARCH_GROUP_SENTENCE)
            ->delete();

        $fragments = SentenceFragment::select(
            'sentence_fragments.id',
            'sentence_fragments.sentence_id',
            'sentence_fragments.fragment',
            'sentences.language_id',
            'glosses.word_id',
            'glosses.word_id',
            'glosses.gloss_group_id',
            'words.word'
        ) //
        ->join('sentences', 'sentences.id', '=', 'sentence_id') //
        ->join('glosses', 'glosses.id', '=', 'sentence_fragments.gloss_id') //
        ->join('words', 'words.id', '=', 'glosses.word_id') //
        ->distinct() //
        ->cursor();
    
        $count = 0;
        foreach ($fragments as $fragment) {
            $keyword                     = StringHelper::clean(StringHelper::toLower($fragment->fragment));
            $word                        = StringHelper::clean($fragment->word);
            $normalizedKeyword           = StringHelper::normalize($keyword, true);
            $normalizedKeywordUnaccented = StringHelper::normalize($keyword, false);

            $normalizedKeywordReversed           = strrev($normalizedKeyword);
            $normalizedKeywordUnaccentedReversed = strrev($normalizedKeywordUnaccented);

            $data = [
                'search_group'                           => SearchKeyword::SEARCH_GROUP_SENTENCE,
                'keyword'                                => $keyword,
                'normalized_keyword'                     => $normalizedKeyword,
                'normalized_keyword_unaccented'          => $normalizedKeywordUnaccented,
                'normalized_keyword_reversed'            => $normalizedKeywordReversed,
                'normalized_keyword_reversed_unaccented' => $normalizedKeywordUnaccentedReversed,
                'keyword_length'                         => mb_strlen($keyword),
                'normalized_keyword_length'              => mb_strlen($normalizedKeyword),
                'normalized_keyword_unaccented_length'   => mb_strlen($normalizedKeywordUnaccented),
                'normalized_keyword_reversed_length'     => mb_strlen($normalizedKeywordUnaccented),
                'normalized_keyword_reversed_unaccented_length' => mb_strlen($normalizedKeywordUnaccentedReversed),
                'entity_name'    => Morphs::getAlias(SentenceFragment::class),
                'entity_id'      => $fragment->id,
                'is_old'         => 0,
                'word'           => $word,
                'word_id'        => $fragment->word_id,
                'gloss_group_id' => $fragment->gloss_group_id,
                'language_id'    => $fragment->language_id,
                'speech_id'      => null
            ];
            SearchKeyword::create($data);
            $count += 1;
            unset($data);

            $this->info(sprintf('%d (sentence): %s -> %s done', $fragment->sentence_id, $fragment->word, $fragment->fragment));
        }

        return $count;
    }
}
