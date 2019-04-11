<?php

namespace App\Repositories;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use DB;
use Exception;
use BadMethodCallException;

use App\Http\Discuss\ContextFactory;
use App\Models\Initialization\Morphs;
use App\Events\{
    ForumPostCreated,
    ForumPostEdited,
    ForumPostLikeCreated
};
use App\Models\{
    Account,
    ForumDiscussion,
    ForumGroup,
    ForumThread,
    ForumPost,
    ForumPostLike,
    ModelBase
};
use App\Repositories\ValueObjects\{
    ForumPostsInThreadValue,
    ForumThreadForEntityValue,
    ForumThreadMetadataValue,
    ForumThreadsInGroupValue,
    ForumThreadValue
};

class DiscussRepository
{
    private $_contextFactory;

    public function __construct(ContextFactory $contextFactory)
    {
        $this->_contextFactory = $contextFactory;
    }

    /**
     * Gets an associative array with the following keys:
     * * `groups`: all available groups in the system.
     * 
     * @return Collection
     */
    public function getGroups() 
    {
        $groups = ForumGroup::orderBy('name')->get();
        return $groups;
    }

    /**
     * Gets an associative array with the following keys:
     * * `group`: the ForumGroup with the corresponding `$groupId`.
     *
     * @param integer $groupId
     * @return ForumGroup
     */
    public function getGroup(int $groupId)
    {
        $group = ForumGroup::findOrFail($groupId);
        return $group;
    }

    /**
     * Gets an associative array with the following keys:
     * * `current_page`: the page for which the threads correspond.
     * * `group`: the ForumGroup (`$group`) specified in the request.
     * * `threads`: a curated array of ForumThread associated with the `$group`.
     * * `no_of_pages`: number of pages for the pagination of threads.
     * * `pages`: an array of pages from 0 to `no_of_pages - 1`.
     *
     * @param ForumGroup $group
     * @param Account $account
     * @param integer $pageNumber
     * @return ForumThreadsInGroupValue
     */
    public function getThreadDataInGroup(ForumGroup $group, Account $account = null, int $pageNumber = 0)
    {
        $this->resolveAccount($account);

        $noOfThreadsPerPage = config('ed.forum_thread_resultset_max_length');
        $noOfPages = intval(ceil(ForumThread::inGroup($group->id)->count() / $noOfThreadsPerPage));
        $currentPage = min($noOfPages, max(1, intval($pageNumber)));

        $threads = ForumThread::inGroup($group->id)
            ->with('account')
            ->orderBy('is_sticky', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->skip(($currentPage - 1) * $noOfThreadsPerPage)
            ->take($noOfThreadsPerPage)
            ->get();

        // Filter out threads that the user is not authorized to see.
        $threads = $threads->filter(function ($thread) use($account) {
            return $this->checkThreadAuthorization($account, $thread);
        });

        $pages = $this->createPageArray($noOfPages);

        return new ForumThreadsInGroupValue([
            'current_page'  => $currentPage,
            'group'         => $group,
            'threads'       => $threads,
            'no_of_pages'   => $noOfPages,
            'pages'         => $pages
        ]);
    }

    /**
     * Gets an associative array with the following keys:
     * * `context`: the context associated with the thread with the corresponding `$threadId`.
     * * `thread`: the thread with the corresponding `$threadId`.
     *
     * @param integer $threadId
     * @return ForumThreadValue
     */
    public function getThreadData(int $threadId)
    {
        $thread = ForumThread::findOrFail($threadId);
        $context = $this->_contextFactory->create($thread->entity_type);

        return new ForumThreadValue([
            'context' => $context,
            'thread'  => $thread
        ]);
    }

    /**
     * Gets an associative array with the following keys:   
     * * `posts`: contains a curated array of posts associated with the specified thread.
     * * `current_page`: an integer denoting the current page.
     * * `pages`: an array of pages from 0 to `no_of_pages - 1`.
     * * `no_of_pages`: number of pages for the pagination of posts within the thread.
     * 
     * @param ForumThread $thread
     * @param Account $account
     * @param string $direction
     * @param integer $pageNumber
     * @param integer $jumpToId
     * @return ForumPostsInThreadValue
     */
    public function getPostDataInThread(ForumThread $thread, Account $account = null, $direction = 'desc',
        int $pageNumber = 0, int $jumpToId = 0)
    {
        $this->resolveAccount($account);
        if (! $this->checkThreadAuthorization($account, $thread)) {
            if ($account === null) {
                throw new AuthenticationException;
            }

            abort(403);
        }

        $loadingOptions = [
            // eager load _account_, but grab only information relevant for the view.
            'account' => function ($query) {
                $query->select('id', 'nickname', 'has_avatar', 'tengwar');
            }
        ];

        if ($account !== null) {
            // has the current user liked the post? Don't care about the rest.
            $loadingOptions['forum_post_likes'] = function ($query) use ($account) {
                $query->where('account_id', $account->id)
                    ->select('account_id', 'forum_post_id');  
            };
        }

        // Direction is either descending (default) or ascending.
        // Ascending order results in the largest ID being the major ID, whereas
        // descending order results in the smallest ID being the major ID.
        if (! in_array($direction, ['asc', 'desc'])) {
            throw new Exception('Invalid sort order - expected "asc" or "desc".');
        }
        $ascending = $direction === 'asc';

        // Retrieve the maximum size of the result set, and determine whether
        // the major ID should be initialized (see above) or retrieved from the
        // input parameters.
        $maxLength = config('ed.forum_resultset_max_length');

        // Determine the number of 'pages' there are, which is relevant when
        // retrieving things in an ascending order.
        $noOfPages = 0;
        if ($ascending) {
            $noOfPages = ceil($thread->forum_posts()
                ->where('is_hidden', 0)
                ->count() / $maxLength
            );
        }

        // composer "filters" (where-conditions for the query fetching the posts). This is a
        // quite interesting process, as it depends entirely on the sort order:
        //
        // ASC (ascending):   The API offers a pagination as a means to sift through posts. The
        //                    default state is nonetheless the _n_ latest posts, assuming that the
        //                    client is interested in the _latest_ posts, albeit presented in an
        //                    ascending order. The major ID, in this situation, acts as the page number.
        //
        // DESC (descending): The API offers an infinite scroll-like experience, where pageNumber is 
        //                    always the least ID of the result set. The result set is 'paginated'
        //                    by the client continuously sending the last, least major ID to the API.
        // 
        // Hidden posts are automatically excluded. Deleted posts might still be shown, which is why
        // we are not filtering out deleted.
        $filters = [
            ['is_hidden', 0],
            ['is_deleted', 0]
        ];

        $skip = 0;
        if ($ascending) {
            if ($jumpToId !== 0) {
                // if the the client is, in fact, requesting to oad a specific post, we 
                // must determine the page on which the post can be found.
                $pageNumber = $noOfPages;
                do {
                    // retrieve an array of IDs within each page, until the page
                    // with the sought-after ID is found.
                    $ok = $thread->forum_posts()->where($filters)
                        ->orderBy('id', 'asc')
                        ->skip(($pageNumber - 1) * $maxLength)
                        ->take($maxLength)
                        ->pluck('id')
                        ->search($jumpToId);

                    if ($ok !== false) {
                        break;
                    }

                    $pageNumber -= 1;
                } while ($pageNumber > 1);
            }

            // The default page should always be the last page, as it is what the user is interested in seeing (= most recent).
            if ($pageNumber <= 0) {
                $pageNumber = $noOfPages;
            }
            $skip = ($pageNumber - 1) * $maxLength;

        } else {
            throw new BadMethodCallException(sprintf('%s is currently not supported.', $direction));
        }

        // The first post in the thread is always the first element of the posts collection.
        // Note how we deliberately do not apply filters here (i.e. is_deleted or is_hidden).
        $firstPostInThread = $thread->forum_posts()
            ->with($loadingOptions)
            ->orderBy('id', 'asc')
            ->first();
        $firstPostInThreadId = 0;
        
        // Create an empty collection in the event that the thread is empty (i.e. all posts have been deleted)
        $posts = new Collection();
        if ($firstPostInThread !== null) {
            $query = $thread->forum_posts()
                ->with($loadingOptions)
                ->where($filters)
                ->orderBy('id', $direction);

            if ($skip > 0) {
                $query = $query->skip($skip);
            }

            if ($maxLength > 0) {
                $query = $query->take($maxLength);
            }

            $posts = $query->get();

            // Prepend the first post in the thread to the resulting collection if it does not already exist.
            $firstPostInThreadId = $firstPostInThread->id;
            if (! $posts->contains(function ($post) use ($firstPostInThreadId) {
                return $post->id === $firstPostInThreadId;
            })) {
                $posts->prepend($firstPostInThread);
            }
        }

        $pages = $this->createPageArray($noOfPages);

        return new ForumPostsInThreadValue([
            'posts'          => $posts,
            'current_page'   => $pageNumber,
            'pages'          => $pages,
            'no_of_pages'    => $noOfPages,
            'thread_id'      => $thread->id ?: null,
            'thread_post_id' => $firstPostInThreadId,
            'jump_post_id'   => $jumpToId
        ]);
    }

    /**
     * Gets the latest threads in Discuss, regardless of groups.
     * @return Collection
     */
    public function getLatestThreads()
    {
        $threads = ForumThread::orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return $threads;
    }

    /**
     * Gets a thread entity (either existing thread or a new instance of a thread) associated with the specified entity.
     * @param ModelBase|string $entityType
     * @param int $id 
     * @param boolean $createIfNotExists
     * @param Account $account
     * @return ForumThreadForEntityValue
     */
    public function getThreadDataForEntity($entityType, int $id = null, $createIfNotExists = false, Account $account = null)
    {
        if (is_object($entityType) && $entityType instanceof ModelBase) {
            $id = $entityType->id;
            $entityType = Morphs::getAlias($entityType);

        } else if (! is_string($entityType)) {
            throw new Exception(sprintf('Unsupported entity %s.', serialize($entityType)));
        }

        if (! is_numeric($id) || $id === 0) {
            throw new Exception('$id is a required parameter.');
        }

        $forumPostId = 0;

        // if the entity is a post, the thread is available as a relation on the ForumPost entity
        if ($entityType === Morphs::getAlias(ForumPost::class)) {
            $post = ForumPost::find($id);
            if ($post === null) {
                return null;
            }

            $thread = $post->forum_thread;
            $forumPostId = $post->id;
        } else {
            $context = $this->_contextFactory->create($entityType);
            if ($context === null) {
                throw new Exception(sprintf('Unsupported discuss entity "%s" with ID %d.', $entityType, $id));
            }

            if (! $context->available($id)) {
                throw new AuthenticationException;
            }

            $data = [
                'entity_type' => $entityType,
                'entity_id'   => $id
            ];
            $thread = ForumThread::where($data)->first();

            if ($thread === null) {
                if (! $createIfNotExists) {
                    return null;
                }

                $this->resolveAccount($account);
                if ($account === null) {
                    return null;
                }

                $entity = $context->resolveById($id);
                if ($entity === null) {
                    return null;
                }

                if ($entity->id === 0) {
                    $entity->account_id = $account->id;
                    $entity->save();
                }

                $defaultGroup = $this->getDefaultForumGroupByEntity($entityType);
                $thread = new ForumThread([
                    'account_id'     => $account->id,
                    'entity_id'      => $entity->id,
                    'entity_type'    => $entityType,
                    'forum_group_id' => $defaultGroup->id
                ]);
            }
        }

        return new ForumThreadForEntityValue([
            'thread' => $thread,
            'forum_post_id' => $forumPostId
        ]);
    }

    /**
     * Gets the default forum group associated with the specified entity type.
     * @param string $entityType entity type ("morph")
     * @return ForumGroup
     */
    public function getDefaultForumGroupByEntity(string $entityType)
    {
        $group = ForumGroup::where('role', $entityType)
                ->first();

        // If no forum group is associated with the specified entity, then defer to the default
        // 'catch all' entity, `ForumDiscussion`.
        if ($group === null) {
            $discussionMorph = Morphs::getAlias(ForumDiscussion::class);
            $group = ForumGroup::where('role', $discussionMorph)
                ->first();

            if ($group === null) {
                throw new Exception(sprint('No forum group is configured for %s.', $discussionMorph));
            }
        }

        return $group;
    }

    public function getThreadMetadataData(int $threadId, array $postIds, Account $account = null)
    {
        $this->resolveAccount($account);

        $allLikes = ForumPostLike::whereIn('forum_post_id', $postIds)
            ->get();

        $likedByAccount = [];
        if ($account !== null) {
            $likedByAccount = $allLikes->reduce(function ($carry, $l) use ($account) {
                if ($l->account_id === $account->id) {
                    $carry[] = $l->forum_post_id;
                }

                return $carry;
            }, []);
        }

        $countPerPost = $allLikes->countBy(function ($l) {
            return $l->forum_post_id;
        });
        $countPerPostIds = $countPerPost->keys();
        $missingPostIds = array_filter($postIds, function ($id) use($countPerPostIds) {
            return ! $countPerPostIds->contains($id);
        });

        foreach ($missingPostIds as $postId) {
            $countPerPost[$postId] = 0;
        }

        return new ForumThreadMetadataValue([
            'forum_post_id' => $postIds,
            'likes' => $likedByAccount,
            'likes_per_post' => $countPerPost
        ]);
    }

    /**
     * @return ForumPost
     */
    public function getPost(int $postId, Account $account = null, $includeDeleted = false)
    {
        $this->resolveAccount($account);

        $post = ForumPost::where([
                ['id', $postId],
                ['is_hidden', 0]
            ])->first();

        if ($post === null) {
            return null;
        }

        if ($post->is_deleted) {
            if (! $includeDeleted || ! $this->checkPostAuthorization($account, $post)) {
                return null;
            }

        } else if (! $this->checkThreadAuthorization($account, $post->forum_thread)) {
            return null;
        }
        
        $post->load('account');
        return $post;
    }

    public function saveThread(ForumThread $thread)
    {
        if (empty($thread->subject)) {
            throw new Exception('You must specify a subject before you can save the thread.');
        }

        if ($thread->entity === null) {
            throw new Exception('You must associate a thread with an entity.');
        }

        $thread->save();
    }

    /**
     * Stores the post with as a reply to the the thread.
     * @param ForumPost $originalPost reference to the post to be stored (and ultimately stored) in the database.
     * @param ForumThread $thread the thread that the post should be associated with
     * @param Account $account (optional) post author
     * @return boolean
     */
    public function savePost(ForumPost &$originalPost, ForumThread $thread, Account $account = null)
    {
        $this->resolveAccount($account);
        
        if ($originalPost->exists && ! $this->checkPostAuthorization($account, $originalPost, $thread)) {
            return false;
        } else if (! $originalPost->exists && ! $this->checkThreadAuthorization($account, $thread)) {
            return false;
        }

        try {
            DB::beginTransaction();

            $this->saveThread($thread);

            if ($originalPost->exists) {
                // update the existing post.
                $originalPost->save();
            } else {
                $post = ForumPost::create([
                    'account_id'          => $account->id,
                    'forum_thread_id'     => $thread->id,
                    'number_of_likes'     => 0,

                    'content'             => $originalPost->content,
                    'parent_form_post_id' => $originalPost->parent_form_post_id,
                ]);
                
                // Abandon the original post by pointing at the post we just created.
                $originalPost = $post;
            }

            $thread->account_id = $account->id;
            $this->updateForumThread($thread);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

        event(new ForumPostCreated($post, $account->id));
        return true;
    }

    public function saveLike(int $postId, Account $account = null)
    {
        $this->resolveAccount($account);
        if ($account === null) {
            return false;
        }

        $post = ForumPost::find($postId);
        if ($post === null) {
            return false;
        }

        $like = null;
        try {
            DB::beginTransaction();

            $like = ForumPostLike::forAccount($account)
                ->where('forum_post_id', $post->id)
                ->first();
            
            if ($like === null) {
                $like = ForumPostLike::create([
                    'account_id' => $account->id,
                    'forum_post_id' => $post->id
                ]);
            } else {
                $like->delete();
                $like = null;
            }

            // Count number of likes on a post level
            $post->number_of_likes = $post->forum_post_likes()->count();
            $post->save();

            // Count number of likes on a thread level
            $this->updateForumThread($post->forum_thread);

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

        // notify only when a like is created.
        if ($like !== null) {
            event(new ForumPostLikeCreated($post, $account->id));
        }

        return $like;
    }

    public function deletePost(ForumPost $post, Account $account = null)
    {
        $this->resolveAccount($account);
        if (! $this->checkPostAuthorization($account, $post)) {
            return false;
        }

        try {
            DB::beginTransaction();

            $post->is_deleted = 1;
            $post->is_hidden = 0;
            $post->save();

            $this->updateForumThread($post->forum_thread);

            DB::commit();
        }
        catch (Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

        return true;
    }

    private function updateForumThread(ForumThread $thread)
    {
        $postIds = $thread->forum_posts() //
            ->orderBy('created_at', 'asc')
            ->where([
                ['is_deleted', '<>', 1],
                ['is_hidden', '<>', 1]
            ]) //
            ->select('id') //
            ->pluck('id');

        $noOfPosts = $postIds->count();
        $noOfLikes = $noOfPosts === 0 //
            ? 0 : ForumPostLike::whereIn('forum_post_id', $postIds)->count();

        // Handle deletion case - do not 'bump' the thread when people remove their posts.
        if ($thread->number_of_posts >= $noOfPosts) {
            $thread->timestamps = false;
        }

        $thread->number_of_posts = $noOfPosts;
        $thread->number_of_likes = $noOfLikes;

        // Make sure that `account_id` reflects the `account_id` for the last record associated with
        // the given thread.
        if ($noOfPosts > 0) {
            $postId = $postIds->last();
            $latest = ForumPost::where('id', $postId) //
                ->select('account_id') //
                ->pluck('account_id');

            $thread->account_id = $latest->first();
        }

        $thread->save();
    }

    /**
     * Updates the reference to point to the account associated with the request, if the current reference is `null`.
     *
     * @param Account $account
     * @return void
     */
    private function resolveAccount(Account &$account = null)
    {
        if ($account === null) {
            $account = Auth::user();
        }
    }

    private function checkPostAuthorization(?Account $account, ForumPost $post, ForumThread $thread = null)
    {
        if ($thread === null) {
            $thread = $post->forum_thread;
        }

        if (! $this->checkThreadAuthorization($account, $thread)) {
            return false;
        }

        if ($post->id === 0) {
            return true;
        }
        
        return $post->account_id === $account->id || $account->isAdministrator();
    }

    /**
     * Determines whether the specified thread `$thread` can be accessed by `$account`.
     *
     * @param ForumThread $thread
     * @param Account $account
     * @return bool
     */
    private function checkThreadAuthorization(?Account $account, ForumThread $thread)
    {
        $context = $this->_contextFactory->create($thread->entity_type);
        return $context->available($thread->entity_id, $account);
    }

    /**
     * Creates an array of incrementing numbers from 1 to `$noOfPages`. `0` is supported.
     *
     * @param integer $noOfPages
     * @return void
     */
    private function createPageArray(int $noOfPages)
    {
        $pages = [];
        for ($i = 0; $i < $noOfPages; $i += 1) {
            $pages[$i] = $i + 1;
        }

        return $pages;
    }
}
