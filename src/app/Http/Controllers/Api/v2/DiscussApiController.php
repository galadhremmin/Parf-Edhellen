<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Repositories\DiscussRepository;

class DiscussApiController extends Controller 
{
    const DEFAULT_SORT_BY_DATE_ORDER = 'asc';

    protected $_discussRepository;

    public function __construct(DiscussRepository $discussRepository)
    {
        $this->_discussRepository = $discussRepository;
    }

    public function groups(Request $request)
    {
        return $this->_discussRepository->getGroups();
    }

    public function groupAndThreads(Request $request, int $groupId)
    {
        $group = $this->_discussRepository->getGroup($groupId);
        $page = $this->getPage($request);
        $user = $request->user();

        return $this->_discussRepository->getThreadsInGroup($group['group'], $user, $page);
    }

    public function latestThreads(Request $request)
    {
        return $this->_discussRepository->getLatestThreads();
    }

    public function thread(Request $request, int $threadId)
    {
        $thread = $this->_discussRepository->getThread($threadId);
        $page = $this->getPage($request);
        $user = $request->user();

        $posts = $this->_discussRepository->getPostsInThread($thread['thread'], $user,
            self::DEFAULT_SORT_BY_DATE_ORDER, $page);
        return $thread + $posts;
    }

    private function getPage(Request $request)
    {
        $params = $request->validate([
            'offset' => 'sometimes|numeric'
        ]);

        return isset($params['offset'])
            ? $params['offset'] 
            : 0;
    }
}
