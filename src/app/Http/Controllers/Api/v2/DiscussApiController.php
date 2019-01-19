<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Repositories\DiscussRepository;

class DiscussApiController extends Controller 
{
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
        return $this->_discussRepository->getThreadsInGroup($groupId, $request->user());
    }

    public function latestThreads(Request $request)
    {
        return $this->_discussRepository->getLatestThreads();
    }

    public function thread(Request $request, int $threadId)
    {
        $params = $request->validate([
            'major_id' => 'sometimes|numeric'
        ]);

        $thread = $this->_discussRepository->getThread($threadId);
        $posts = $this->_discussRepository->getPostsInThread($thread['thread'], $request->user(), 
            'asc', isset($params['major_id']) ? $params['major_id'] : 0);
        return $thread + $posts;
    }
}
