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
    public function store(Request $request)
    {
        $data = (object) $this->validate($request, [
            'content'             => 'required|string',
            'entity_id'           => 'sometimes|number',
            'entity_type'         => 'sometimes|string',
            'forum_group_id'      => 'sometimes|number|exists:forum_groups,id',
            'is_sticky'           => 'sometimes|boolean',
            'parent_form_post_id' => 'sometimes|numeric|exists:forum_posts,id',
            'subject'             => 'sometimes|string'
        ]);

        dd($data);

        $comments = $request->input('comments');
        $parentEntityId = null;
        if ($request->has('parent_form_post_id')) {
            $parentEntityId = $request->input('parent_form_post_id');
        }

        $thread = $this->getOrNewForumThread($request);

        // Update the thread with information pertaining to the post just published.
        $account = $request->user();
        $thread->account_id = $account->id;
        $thread->number_of_posts += 1;
        $thread->save();

        $post = ForumPost::create([
            'forum_thread_id'     => $thread->id,
            'account_id'          => $account->id,
            'content'             => $comments,
            'parent_form_post_id' => $parentEntityId,
            'number_of_likes'     => 0
        ]);

        // Register an audit trail
        event(new ForumPostCreated($post, $account->id));

        return response(null, 201);
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
