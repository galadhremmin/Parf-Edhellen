<?php

namespace App\Repositories\SearchIndexResolvers;

use Illuminate\Support\Collection;
use App\Adapters\SentenceAdapter;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\{
    Account,
    Sentence,
    SentenceFragment
};
use App\Models\Initialization\Morphs;

class SentenceSearchIndexResolver extends SearchIndexResolverBase
{
    private $_sentenceAdapter;
    private $_fragmentMorphName;

    public function __construct(SentenceAdapter $adapter)
    {
        $this->_sentenceAdapter   = $adapter;
        $this->_fragmentMorphName = Morphs::getAlias(SentenceFragment::class);
    }

    public function resolveByQuery(array $params, SearchIndexSearchValue $value): array
    {
        $entityIds = $params['query'] //
            ->select('entity_id')
            ->where('entity_name', $this->_fragmentMorphName)
            ->orderBy($params['search_column'], 'asc')
            ->limit(100)
            ->pluck('entity_id');

        if ($entityIds->isEmpty()) {
            $sentences = new Collection();

        } else {
            $sentenceIds = SentenceFragment::whereIn('id', $entityIds)
                ->select('sentence_id')
                ->distinct()
                ->pluck('sentence_id');
            
            $sentences = Sentence::whereIn('id', $sentenceIds)
                ->select('id', 'name', 'description', 'language_id', 'is_neologism', 'account_id', 'source')
                ->get();

            $accounts = Account::whereIn('id', $sentences->pluck('account_id')->unique())
                ->select('id', 'nickname')
                ->get()
                ->groupBy('id');

            foreach ($sentences as $sentence) {
                if (isset($accounts[$sentence->account_id])) {
                    $sentence->account = $accounts[$sentence->account_id][0];
                }
            }
        }

        return $this->_sentenceAdapter->adaptSentence($sentences);
    }
}
