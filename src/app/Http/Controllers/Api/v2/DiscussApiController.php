<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\{
    ForumPost
};
use App\Repositories\DiscussRepository;

class DiscussApiController extends Controller 
{
    const DEFAULT_SORT_BY_DATE_ORDER = 'asc';
    const PARAMETER_FORUM_POST_CONTENT = 'content';
    const PARAMETER_FORUM_THREAD_ID = 'forum_thread_id';
    const PARAMETER_FORUM_POST_ID = 'forum_post_id';
    const PARAMETER_PAGE_NUMBER   = 'offset';

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

        // ForumPost ID is an optional parameter that can be specified by the consumer when
        // they want to 'jump' to a specific forum post.
        $postId = 0;
        $data = $request->validate([
            self::PARAMETER_FORUM_POST_ID => 'sometimes|numeric|exists:forum_posts,id'
        ]);
        if (isset($data[self::PARAMETER_FORUM_POST_ID])) {
            $postId = intval($data[self::PARAMETER_FORUM_POST_ID]);
        }

        $posts = $this->_discussRepository->getPostsInThread($thread['thread'], $user,
            self::DEFAULT_SORT_BY_DATE_ORDER, $page, $postId);
        return $thread + $posts;
    }

    public function resolveThread(Request $request, string $entityType, int $entityId)
    {
        return [ $entityType => $entityId ];
    }

    /**
     * HTTP POST. Creates a new forum post.
     *            Caller must be authenticated.
     *
     * @param Request $request
     * @return response 201 on success
     */
    public function storePost(Request $request)
    {
        $data = $this->validate($request, [
            self::PARAMETER_FORUM_POST_CONTENT => 'required|string',
            self::PARAMETER_FORUM_THREAD_ID    => 'required|numeric|exists:forum_threads,id',
            //'forum_group_id'      => 'sometimes|numeric|exists:forum_groups,id',
            //'is_sticky'           => 'sometimes|boolean',
            //'parent_form_post_id' => 'sometimes|numeric|exists:forum_posts,id',
            //'subject'             => 'sometimes|string'
        ]);

        $threadData = $this->_discussRepository->getThread($data[self::PARAMETER_FORUM_THREAD_ID]);
        $thread = $threadData['thread'];

        $post = new ForumPost([
            self::PARAMETER_FORUM_POST_CONTENT => $data[self::PARAMETER_FORUM_POST_CONTENT]
        ]);
        $ok = $this->_discussRepository->savePost($post, $thread, $request->user());
        if (! $ok) {
            return response(null, 400);
        }

        $post->makeHidden(['forum_thread']);
        return [
            'post' => $post,
            'thread' => $thread
        ];
    }

    private function getPage(Request $request)
    {
        $params = $request->validate([
            self::PARAMETER_PAGE_NUMBER => 'sometimes|numeric'
        ]);

        return isset($params[self::PARAMETER_PAGE_NUMBER])
            ? $params[self::PARAMETER_PAGE_NUMBER] 
            : 0;
    }
}