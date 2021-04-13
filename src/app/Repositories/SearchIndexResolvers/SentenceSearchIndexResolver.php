<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Adapters\SentenceAdapter;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\{
    Account,
    Sentence,
    SentenceFragment
};

class SentenceSearchIndexResolver extends SearchIndexResolverBase
{
    public function __construct(SentenceAdapter $adapter)
    {
        $this->_sentenceAdapter = $adapter;
    }

    public function resolveByQuery(array $params, SearchIndexSearchValue $value): array
    {
        $entityIds = $params['query'] //
            ->select('entity_id')
            ->where('entity_name', 'fragment')
            ->orderBy($params['search_column'], 'asc')
            ->limit(100)
            ->pluck('entity_id');

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
            $sentence->account = $accounts[$sentence->account_id][0];
        }

        return $this->_sentenceAdapter->adaptSentence($sentences);
    }
}
