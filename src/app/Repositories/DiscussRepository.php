<?php

namespace App\Repositories;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

use DB;
use Exception;
use BadMethodCallException;

use App\Adapters\DiscussAdapter;
use App\Http\Discuss\ContextFactory;
use App\Models\Initialization\Morphs;
use App\Models\{
    Account,
    ForumDiscussion,
    ForumGroup,
    ForumThread,
    ForumPost,
    ForumPostLike
};
use App\Events\{
    ForumPostCreated,
    ForumPostEdited,
    ForumPostLikeCreated
};
use Illuminate\Database\Eloquent\Collection;

class DiscussRepository
{
    private $_contextFactory;
    private $_discussAdapter;

    public function __construct(ContextFactory $contextFactory, DiscussAdapter $discussAdapter,
        MailSettingRepository $mailSettingRepository) {
        $this->_contextFactory = $contextFactory;
        $this->_discussAdapter = $discussAdapter;
    }

    /**
     * Gets an associative array with the following keys:
     * * `groups`: all available groups in the system.
     * 
     * @return array
     */
    public function getGroups() 
    {
        $groups = ForumGroup::orderBy('name')->get();
        return [
            'groups' => $groups
        ];
    }

    /**
     * Gets an associative array with the following keys:
     * * `group`: the ForumGroup with the corresponding `$groupId`.
     *
     * @param integer $groupId
     * @return array
     */
    public function getGroup(int $groupId)
    {
        $group = ForumGroup::findOrFail($groupId);
        return [
            'group' => $group
        ];
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
     * @return array
     */
    public function getThreadsInGroup(ForumGroup $group, Account $account = null, int $pageNumber = 0)
    {
        $this->resolveUser($account);

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
            return $this->checkThreadAuthorization($thread, $account);
        });
        $this->_discussAdapter->adaptThreads($threads);

        $pages = $this->createPageArray($noOfPages);

        return [
            'current_page' => $currentPage,
            'group' => $group,
            'threads' => $threads,
            'no_of_pages' => $noOfPages,
            'pages' => $pages
        ];
    }

    /**
     * Gets an associative array with the following keys:
     * * `context`: the context associated with the thread with the corresponding `$threadId`.
     * * `thread`: the thread with the corresponding `$threadId`.
     *
     * @param integer $threadId
     * @return array
     */
    public function getThread(int $threadId)
    {
        $thread = ForumThread::findOrFail($threadId);
        $context = $this->_contextFactory->create($thread->entity_type);

        return [
            'context' => $context,
            'thread' => $thread
        ];
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
     * @return array
     */
    public function getPostsInThread(ForumThread $thread, Account $account = null, $direction = 'desc',
        int $pageNumber = 0, int $jumpToId = 0)
    {
        $this->resolveUser($account);
        if (! $this->checkThreadAuthorization($thread, $account)) {
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
            $loadingOptions['likes'] = function ($query) use ($account) {
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
            ['is_hidden', 0]
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

            // Adapt the posts for the view
            $this->_discussAdapter->adaptPosts($posts);
        }

        $pages = $this->createPageArray($noOfPages);

        return [
            'posts'          => $posts,
            'current_page'   => $pageNumber,
            'pages'          => $pages,
            'no_of_pages'    => $noOfPages,
            'thread_id'      => $thread->id ?: null,
            'thread_post_id' => $firstPostInThreadId,
            'jump_post_id'   => $jumpToId
        ];
    }

    /**
     * Gets the latest threads in Discuss, regardless of groups.
     */
    public function getLatestThreads()
    {
        $threads = ForumThread::orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        $this->_discussAdapter->adaptThreads($threads);

        return [
            'threads' => $threads
        ];
    }

    /**
     * Gets a thread entity (either existing thread or a new instance of a thread) associated with the specified entity.
     */
    public function getThreadForEntity(string $entityType, int $id, $createIfNotExists = false, Account $account = null)
    {
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

                $this->resolveUser($account);
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

        return [
            'thread' => $thread,
            'forum_post_id' => $forumPostId
        ];
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

    public function getPost(int $postId, Account $account = null)
    {
        $this->resolveUser($account);

        $post = ForumPost::find($postId);
        if ($post === null) {
            return null;
        }

        if (! $this->checkThreadAuthorization($post->forum_thread, $account)) {
            return null;
        }

        $this->_discussAdapter->adaptPost($post);
        return [
            'post' => $post,
        ];
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
        $this->resolveUser($account);

        if (! $this->checkThreadAuthorization($thread, $account)) {
            return false;
        }

        try {
            DB::beginTransaction();

            $post = ForumPost::create([
                'account_id'          => $account->id,
                'forum_thread_id'     => $thread->id,
                'number_of_likes'     => 0,

                'content'             => $originalPost->content,
                'parent_form_post_id' => $originalPost->parent_form_post_id,
            ]);

            $thread->account_id = $account->id;
            $thread->number_of_posts = $thread->forum_posts->count();
            $thread->save();

            DB::commit();

            // Abandon the original post by pointing at the post we just created.
            $originalPost = $post;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

        event(new ForumPostCreated($post, $account->id));
        return true;
    }

    public function getMetadata(int $threadId, array $postIds, Account $account = null)
    {
        $this->resolveUser($account);

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

        return [
            'forum_post_id' => $postIds,
            'likes' => $likedByAccount,
            'likes_per_post' => $countPerPost
        ];
    }

    public function saveLike(int $postId, Account $account = null)
    {
        $this->resolveUser($account);
        if ($account === null) {
            return false;
        }

        $like = ForumPostLike::forAccount($account)
            ->where('forum_post_id', $postId)
            ->first();
        
        if ($like === null) {
            $like = ForumPostLike::create([
                'account_id' => $account->id,
                'forum_post_id' => $postId
            ]);
        } else {
            $like->delete();
            $like = null;
        }

        return $like;
    }

    /**
     * Updates the reference to point to the account associated with the request, if the current reference is `null`.
     *
     * @param Account $account
     * @return void
     */
    private function resolveUser(Account &$account = null)
    {
        if ($account === null) {
            $account = Auth::user();
        }
    }

    /**
     * Determines whether the specified thread `$thread` can be accessed by `$account`.
     *
     * @param ForumThread $thread
     * @param Account $account
     * @return bool
     */
    private function checkThreadAuthorization(ForumThread $thread, Account $account = null)
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
