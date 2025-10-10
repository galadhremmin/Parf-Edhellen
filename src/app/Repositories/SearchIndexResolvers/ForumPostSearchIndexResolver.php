<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Adapters\DiscussAdapter;
use App\Models\ForumPost;
use App\Models\Initialization\Morphs;
use App\Repositories\DiscussRepository;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Repositories\ValueObjects\ForumThreadsForPostsValue;

class ForumPostSearchIndexResolver extends SearchIndexResolverBase
{
    private DiscussRepository $_discussRepository;

    private DiscussAdapter $_discussAdapter;

    private ?string $_forumPostMorphName;

    public function __construct(DiscussRepository $discussRepository, DiscussAdapter $discussAdapter)
    {
        $this->_discussRepository = $discussRepository;
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

    public function emptyResponse(): array
    {
        return $this->_discussAdapter->adaptForSearchResults(new ForumThreadsForPostsValue([
            'forum_threads' => collect([]),
            'forum_groups' => collect([]),
        ]));
    }
}
