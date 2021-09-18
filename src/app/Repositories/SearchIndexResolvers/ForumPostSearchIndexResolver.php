<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Adapters\DiscussAdapter;
use App\Models\Initialization\Morphs;
use App\Models\ForumPost;
use App\Repositories\DiscussRepository;
use App\Repositories\ValueObjects\SearchIndexSearchValue;

class ForumPostSearchIndexResolver extends SearchIndexResolverBase
{
    private $_discussRepository;
    private $_discussAdapter;
    private $_forumPostMorphName;

    public function __construct(DiscussRepository $discussRepository, DiscussAdapter $discussAdapter)
    {
        $this->_discussRepository  = $discussRepository;
        $this->_discussAdapter = $discussAdapter;
        $this->_forumPostMorphName = Morphs::getAlias(ForumPost::class);
    }

    public function resolveByQuery(array $params, SearchIndexSearchValue $value): array
    {
        $entityIds = $params['query'] //
            ->select('entity_id') //
            ->where('entity_name', $this->_forumPostMorphName) //
            ->orderBy($params['search_column'], 'asc') //
            ->limit(100) //
            ->pluck('entity_id') //
            ->toArray();

        $threadData = $this->_discussRepository->getThreadsForPosts($entityIds);
        return $this->_discussAdapter->adaptForSearchResults($threadData);
    }
}
