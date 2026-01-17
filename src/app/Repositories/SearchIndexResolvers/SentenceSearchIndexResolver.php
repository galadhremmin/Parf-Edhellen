<?php

namespace App\Repositories\SearchIndexResolvers;

use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;

use App\Adapters\SentenceAdapter;
use App\Models\Account;
use App\Models\Initialization\Morphs;
use App\Models\Sentence;
use App\Models\SentenceFragment;
use App\Repositories\ValueObjects\SearchIndexSearchValue;

class SentenceSearchIndexResolver extends SearchIndexResolverBase
{
    private SentenceAdapter $_sentenceAdapter;

    private ?string $_fragmentMorphName;

    public function __construct(SentenceAdapter $adapter)
    {
        $this->_sentenceAdapter = $adapter;
        $this->_fragmentMorphName = Morphs::getAlias(SentenceFragment::class);
    }

    public function resolveByQuery(array $params, SearchIndexSearchValue $value): array
    {
        try {
            $entityIds = $params['query'] //
                ->select('entity_id')
                ->where('entity_name', $this->_fragmentMorphName)
                ->orderBy($params['search_column'], 'asc')
                ->limit(100)
                ->pluck('entity_id');
        } catch (QueryException $_) {
            $entityIds = new Collection;
        }

        if ($entityIds->isEmpty()) {
            $sentences = new Collection;

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
                    $sentence->setAttribute('account', $accounts[$sentence->account_id][0]);
                }
            }
        }

        return $this->_sentenceAdapter->adaptSentence($sentences);
    }

    public function emptyResponse(): array
    {
        return $this->_sentenceAdapter->adaptSentence(new Collection);
    }
}
