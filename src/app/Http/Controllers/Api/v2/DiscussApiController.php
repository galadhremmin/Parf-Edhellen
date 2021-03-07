<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\{
    ForumPost
};
use App\Adapters\DiscussAdapter;
use App\Repositories\DiscussRepository;
use App\Helpers\LinkHelper;

class DiscussApiController extends Controller 
{
    const DEFAULT_SORT_BY_DATE_ORDER = 'asc';

    const PARAMETER_PAGE_NUMBER = 'offset';
    const PARAMETER_CREATE = 'create';
    const PARAMETER_ENTITY_TYPE = 'entity_type';
    const PARAMETER_ENTITY_ID = 'entity_id';
    const PARAMETER_FORUM_POST_CONTENT = 'content';
    const PARAMETER_FORUM_POST_SUBJECT = 'subject';
    const PARAMETER_FORUM_THREAD_ID = 'forum_thread_id';
    const PARAMETER_FORUM_THREAD_STICKY = 'sticky';
    const PARAMETER_FORUM_GROUP_ID = 'forum_group_id';
    const PARAMETER_FORUM_POST_ID = 'forum_post_id';
    
    const PROPERTY_THREAD = 'thread';
    const PROPERTY_THREAD_GROUP_COLLECTION = 'groups';
    const PROPERTY_POST = 'post';
    const PROPERTY_POST_LIKE = 'like';
    const PROPERTY_POST_URL = 'postUrl';

    protected $_discussAdapter;
    protected $_discussRepository;

    public function __construct(DiscussAdapter $discussAdapter, DiscussRepository $discussRepository)
    {
        $this->_discussAdapter    = $discussAdapter;
        $this->_discussRepository = $discussRepository;
    }

    public function getGroups()
    {
        return $this->_discussRepository->getGroups();
    }

    public function getGroupAndThreads(Request $request, int $groupId)
    {
        $group = $this->_discussRepository->getGroup($groupId);
        $page = $this->getPageFromRequest($request);
        $user = $request->user();

        $threadData = $this->_discussRepository->getThreadDataInGroup($group, $user, $page);
        $this->_discussAdapter->adaptThreads($threadData->getThreads());

        return $threadData;
    }

    /**
     * Gets the latest threads based on their latest posts.
     */
    public function getLatestThreads(Request $request)
    {
        $threadData = $this->_discussRepository->getLatestThreads();
        $this->_discussAdapter->adaptThreads($threadData);
        return $threadData;
    }

    /**
     * HTTP GET. Gets data for the specified thread.
     */
    public function getThread(Request $request, int $threadId)
    {
        $threadData = $this->_discussRepository->getThreadData($threadId);

        $page = $this->getPageFromRequest($request);
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

        $postData = $this->_discussRepository->getPostDataInThread($threadData->getThread(), $user,
            self::DEFAULT_SORT_BY_DATE_ORDER, $page, $postId);

        $this->_discussAdapter->adaptForumThread($threadData->getThread());
        $this->_discussAdapter->adaptPosts($postData->getPosts());

        return array_merge($threadData->getAllValues(), $postData->getAllValues());
    }

    public function getThreadByEntity(Request $request, string $entityType, int $entityId)
    {
        $threadData = $this->_discussRepository->getThreadDataForEntity($entityType, $entityId, true, $request->user());

        if ($threadData->getThread()->exists) {
            return $this->getThread($request, $threadData->getThread()->id);
        }

        return array_merge(
            $threadData->getAllValues(),
            [
                // no posts are available since the thread does not actually exist.
                'posts' => []
            ]
        );
    }

    /**
     * HTTP GET. Gets data for the specified post.
     */
    public function getPost(Request $request, int $postId)
    {
        $data = $request->validate([
            'include_deleted' => 'sometimes|boolean',
            'markdown' => 'sometimes|boolean'
        ]);

        $includeDeleted = isset($data['include_deleted'])
            ? boolval($data['include_deleted']) : false;

        $account = $request->user();
        $post = $this->_discussRepository->getPost($postId, $account, $includeDeleted);
        if ($post === null) {
            return response(null, 404);
        }

        if (! isset($data['markdown']) || boolval($data['markdown']) === false) {
            $this->_discussAdapter->adaptPost($post);
        }

        return [
            self::PROPERTY_POST => $post
        ];
    }

    /**
     * HTTP GET. Redirects the client to the thread associated with the specified entity.
     */
    public function resolveThread(Request $request, string $entityType, int $entityId)
    {
        $data = $request->validate([
            self::PARAMETER_CREATE => 'sometimes|boolean',
        ]);

        $createIfNotExists = isset($data[self::PARAMETER_CREATE])
            ? boolval($data[self::PARAMETER_CREATE]) : false;
        $threadData = $this->_discussRepository->getThreadDataForEntity($entityType, $entityId, $createIfNotExists);

        if ($threadData === null) {
            return response(null, 404);
        }

        if ($request->ajax()) {
            return $threadData;

        } else {
            $thread = $threadData->getThread();
            $forumPostId = $threadData->getForumPostId();
            $linker = resolve(LinkHelper::class);

            return redirect($linker->forumThread($thread->forum_group_id, $thread->forum_group->name, 
                $thread->id, $thread->normalized_subject, $forumPostId));
        }
    }

    /**
     * HTTP GET. Returns the thread for a specific post.
     */
    public function resolveThreadFromPost(Request $request, int $postId)
    {
        $post = $this->_discussRepository->getPost($postId, $request->user());
        if ($post === null) {
            abort(404, sprintf('Post with ID %d does not exist.', $postId));
        }

        if ($request->ajax()) {
            return $post->forum_thread;
        } else {
            $linker = resolve(LinkHelper::class);
            return redirect($linker->forumThread(
                $post->forum_thread->forum_group_id,
                $post->forum_thread->forum_group->name,
                $post->forum_thread_id,
                $post->forum_thread->normalized_subject,
                $post->id
            ));
        }
    }

    /**
     * HTTP POST. Retrieves metadata associated with the specified posts.
     */
    public function getThreadMetadata(Request $request)
    {
        $data = $request->validate([
            self::PARAMETER_FORUM_THREAD_ID => 'required|numeric',
            self::PARAMETER_FORUM_POST_ID.'.*' => 'required|numeric'
        ]);
        $user = $request->user();

        $threadId = intval($data[self::PARAMETER_FORUM_THREAD_ID]);
        $postsId = $data[self::PARAMETER_FORUM_POST_ID];
        return $this->_discussRepository->getThreadMetadataData($threadId, $postsId, $user);
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
        $account = $request->user();
        $data = $request->validate([
            self::PARAMETER_FORUM_POST_CONTENT => 'required|string|min:1',
            self::PARAMETER_FORUM_THREAD_ID    => 'sometimes|numeric|exists:forum_threads,id'
        ]);

        if (isset($data[self::PARAMETER_FORUM_THREAD_ID])) {
            // update an existing thread
            $threadId = intval($data[self::PARAMETER_FORUM_THREAD_ID]);
            $threadData = $this->_discussRepository->getThreadData($threadId);
            $thread = $threadData->getThread();

        } else {
            $subjectRule = 'sometimes|string|min:3|max:512';
            $data = $data + $request->validate([
                self::PARAMETER_ENTITY_TYPE        => 'required|string|min:1|max:16',
                self::PARAMETER_ENTITY_ID          => 'sometimes|numeric',
                self::PARAMETER_FORUM_GROUP_ID     => 'sometimes|numeric|exists:forum_groups,id',
                self::PARAMETER_FORUM_POST_SUBJECT => $subjectRule
            ]);

            $entityType = $data[self::PARAMETER_ENTITY_TYPE];
            $entityId = 0;
            if (isset($data[self::PARAMETER_ENTITY_ID])) {
                $entityId = intval($data[self::PARAMETER_ENTITY_ID]);
            }

            // create a new thread based on the entity specified in the request
            $threadData = $this->_discussRepository->getThreadDataForEntity($entityType, $entityId, true, $account);
            $thread = $threadData->getThread();

            // the default forum group usually comes with an auto generated subject. Replace it with the 
            // subject specified in the request.
            if (isset($data[self::PARAMETER_FORUM_POST_SUBJECT])) {
                $thread->subject = $data[self::PARAMETER_FORUM_POST_SUBJECT];
            } else if (empty($thread->subject)) {
                $request->validate([
                    self::PARAMETER_FORUM_POST_SUBJECT => $subjectRule
                ]);

                abort(422);
            }

            // the thread is assigned a default form group based on the specified entity. However, if the request
            // specifies a specified group, it is here where it may be applied.
            if (isset($data[self::PARAMETER_FORUM_GROUP_ID])) {
                $thread->forum_group_id = intval($data[self::PARAMETER_FORUM_GROUP_ID]);
            }
        }
        
        $post = new ForumPost([
            self::PARAMETER_FORUM_POST_CONTENT => $data[self::PARAMETER_FORUM_POST_CONTENT]
        ]);
        $ok = $this->_discussRepository->savePost($post, $thread, $request->user());
        if (! $ok) {
            return response(null, 403);
        }

        $linkHelper = resolve(LinkHelper::class);
        $postUrl = $linkHelper->forumThread(
            $thread->forum_group_id,
            $thread->forum_group->name,
            $thread->id,
            $thread->normalized_subject,
            $post->id
        );

        $post->makeHidden(['forum_thread']);
        return [
            self::PROPERTY_POST => $post,
            self::PROPERTY_POST_URL => $postUrl,
            self::PROPERTY_THREAD => $thread
        ];
    }

    public function deletePost(Request $request, int $postId)
    {
        $account = $request->user();

        $post = $this->_discussRepository->getPost($postId, $account, true);
        $ok = $this->_discussRepository->deletePost($post, $account);

        return response(null, $ok ? 200 : 400);
    }

    public function updatePost(Request $request, int $postId)
    {
        $account = $request->user();
        $postData = $request->validate([
            self::PARAMETER_FORUM_POST_CONTENT => 'required|string|min:1'
        ]);
        $threadData = $request->validate([
            self::PARAMETER_FORUM_POST_SUBJECT => 'sometimes|string|min:3|max:512'
        ]);

        $post = $this->_discussRepository->getPost($postId, $account);
        if ($post === null) {
            return response(null, 404);
        }

        $post->fill($postData);

        $thread = $post->forum_thread;
        if (count($threadData)) {
            $thread->fill($threadData);
        }

        $ok = $this->_discussRepository->savePost($post, $thread, $account);
        return response(null, $ok ? 200 : 403);
    }

    public function storeLike(Request $request)
    {
        $data = $request->validate([
            self::PARAMETER_FORUM_POST_ID => 'required|numeric|exists:forum_posts,id'
        ]);

        $like = $this->_discussRepository->saveLike($data[self::PARAMETER_FORUM_POST_ID], $request->user());
        if ($like === false) {
            return response(null, 400);
        }

        return [
            self::PROPERTY_POST_LIKE => $like
        ];
    }

    public function updateThreadStickiness(Request $request)
    {
        $data = $request->validate([
            self::PARAMETER_FORUM_THREAD_ID     => 'required|numeric|exists:forum_threads,id',
            self::PARAMETER_FORUM_THREAD_STICKY => 'required|boolean'
        ]);

        $threadId = intval($data[self::PARAMETER_FORUM_THREAD_ID]);
        $sticky   = boolval($data[self::PARAMETER_FORUM_THREAD_STICKY]);
        $this->_discussRepository->saveSticky($threadId, $sticky);

        return [
            self::PARAMETER_FORUM_THREAD_STICKY => $sticky
        ];
    }

    private function getPageFromRequest(Request $request)
    {
        $params = $request->validate([
            self::PARAMETER_PAGE_NUMBER => 'sometimes|numeric'
        ]);

        return isset($params[self::PARAMETER_PAGE_NUMBER])
            ? $params[self::PARAMETER_PAGE_NUMBER] 
            : 0;
    }
}
