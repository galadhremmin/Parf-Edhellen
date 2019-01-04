<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;

use App\Adapters\DiscussAdapter;
use App\Http\Discuss\ContextFactory;
use App\Models\{
    Account,
    ForumGroup,
    ForumThread,
    ForumPost
};

class DiscussRepository
{
    private $_contextFactory;
    private $_discussAdapter;

    public function __construct(ContextFactory $contextFactory, DiscussAdapter $discussAdapter) {
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
        $noOfPages = ceil(ForumThread::inGroup($group->id)->count() / $noOfThreadsPerPage);
        $currentPage = min($noOfPages - 1, max(0, intval($pageNumber)));

        $threads = ForumThread::inGroup($group->id)
            ->with('account')
            ->orderBy('is_sticky', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->skip($currentPage * $noOfThreadsPerPage)
            ->take($noOfThreadsPerPage)
            ->get();
        
        // Filter out threads that the user is not authorized to see.
        $threads = $threads->filter(function ($thread) use($account) {
            return $this->checkThreadAuthorization($thread, $account);
        });

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
     * @param integer $majorId
     * @param integer $jumpToId
     * @return array
     */
    public function getPostsInThread(ForumThread $thread, Account $account = null, $direction = 'desc',
        int $majorId = 0, int $jumpToId = 0)
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
        // DESC (descending): The API offers an infinite scroll-like experience, where majorId is 
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
            // load the _latest_ n posts by default, even when sorting in an ascending 
            // order.
            if ($majorId === 0) {
                $majorId = $noOfPages; // last page
            }
            
            if ($jumpToId !== 0) {
                // if the the client is, in fact, requesting to oad a specific post, we 
                // must determine the page on which the post can be found.
                $majorId = $noOfPages;
                do {
                    // retrieve an array of IDs within each page, until the page
                    // with the sought-after ID is found.
                    $ok = $thread->forum_posts()->where($filters)
                        ->orderBy('id', 'asc')
                        ->skip(($majorId - 1) * $maxLength)
                        ->take($maxLength)
                        ->pluck('id')
                        ->search($jumpToId);

                    if ($ok !== false) {
                        break;
                    }

                    $majorId -= 1;
                } while ($majorId > 1);
            }

            $skip = ($majorId - 1) * $maxLength;

        } else {
            if ($jumpToId !== 0) {
                $filters[] = ['id', '>=', $jumpToId];
                $maxLength = 0; // TODO: implement a means to restrict the result set
            } else if ($majorId !== 0) {
                $filters[] = ['id', '<', $majorId];
            }
            
            if ($majorId === 0) {
                $majorId = PHP_INT_MAX;
            }
        }
        
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

        foreach ($posts as $post) {
            // Determine the major ID depending on the order of the items
            if (! $ascending && $majorId > $post->id) {
                $majorId = $post->id;
            }

            $this->_discussAdapter->adaptPost($post);
        }

        $pages = $this->createPageArray($noOfPages);

        return [
            'posts'        => $posts,
            'current_page' => $majorId,
            'pages'        => $pages,
            'no_of_pages'  => $noOfPages,
            'thread_id'    => $thread->id ?: null
        ];
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
