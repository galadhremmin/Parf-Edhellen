<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Repositories\ForumRepository;
use App\Models\Initialization\Morphs;
use App\Http\Discuss\ContextFactory;
use App\Adapters\DiscussAdapter;
use App\Models\{ 
    Account,
    Contribution,
    ForumPost, 
    ForumPostLike, 
    ForumThread, 
    Translation, 
    Sentence 
};
use App\Events\{
    ForumPostCreated,
    ForumPostEdited,
    ForumPostLikeCreated
};

class ForumApiController extends Controller 
{
    protected $_discussAdapter;
    protected $_repository;
    protected $_contextFactory;

    public function __construct(DiscussAdapter $discussAdapter, 
        ForumRepository $repository, ContextFactory $contextFactory)
    {
        $this->_discussAdapter = $discussAdapter;
        $this->_repository     = $repository;
        $this->_contextFactory = $contextFactory;
    }

    public function index(Request $request)
    {
        $thread = $this->getOrNewForumThread($request);
        if ($thread->id) {
            $loadingOptions = [
                // eager load _account_, but grab only information relevant for the view.
                'account' => function ($query) {
                    $query->select('id', 'nickname', 'has_avatar', 'tengwar');
                }
            ];

            $user = $request->user();
            if ($user !== null) {
                // has the current user liked the post? Don't care about the rest.
                $loadingOptions['likes'] = function ($query) use ($user) {
                    $query->where('account_id', $user->id)
                        ->select('account_id', 'forum_post_id');  
                };
            }

            // Direction is either descending (default) or ascending.
            // Ascending order results in the largest ID being the major ID, whereas
            // descending order results in the smallest ID being the major ID.
            $direction = $request->has('order')
                ? ($request->input('order') === 'asc' ? 'asc' : 'desc')
                : 'desc';
            $ascending = $direction === 'asc';

            // Retrieve the maximum size of the result set, and determine whether
            // the major ID should be initialized (see above) or retrieved from the
            // input parameters.
            $maxLength = config('ed.forum_resultset_max_length');
            $majorId = $request->has('from_id')
                ? intval($request->input('from_id'))
                : -1;

            // Determine the number of 'pages' there are, which is relevant when
            // retrieving things in an ascending order.
            $pages = 0;
            if ($ascending) {
                $pages = ceil($thread->forum_posts()
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
                if ($majorId < 1) {
                    $majorId = $pages; // 1:st page
                } else if ($majorId > $pages) {
                    // if the major ID is larger than the number of pages available,
                    // it is likely that the the client is, in fact, requesting to
                    // load a specific post. In that case, we must determine the page
                    // on which the post might be found. 
                    $requestedId = $majorId;
                    $majorId = $pages;
                    do {
                        // retrieve an array of IDs within each page, until the page
                        // with the sought-after ID is found.
                        $ok = $thread->forum_posts()->where($filters)
                            ->orderBy('id', 'asc')
                            ->skip(($majorId - 1) * $maxLength)
                            ->take($maxLength)
                            ->pluck('id')
                            ->search($requestedId);

                        if ($ok !== false) {
                            break;
                        }

                        $majorId -= 1;
                    } while ($majorId > 1);
                }
                $skip = ($majorId - 1) * $maxLength;

            } else if ($majorId > 0) {
                $filters[] = ['id', '<', $majorId];
            } else {
                $majorId = PHP_INT_MAX;
            }
            
            $posts = $thread->forum_posts()
                ->with($loadingOptions)
                ->where($filters)
                ->orderBy('id', $direction)
                ->skip($skip)
                ->take($maxLength)
                ->get();

            foreach ($posts as $post) {
                // Determine the major ID depending on the order of the items
                if (! $ascending && $majorId > $post->id) {
                    $majorId = $post->id;
                }

                $this->_discussAdapter->adaptPost($post);
            }
        } else {
            $posts = [];
            $majorId = 0;
            $pages = 0;
        }

        return [
            'posts'    => $posts,
            'major_id' => $majorId,
            'pages'    => $pages
        ];
    }

    public function show(Request $request, int $id)
    {
        $post = ForumPost::findOrFail($id);

        $resolver = $this->_contextFactory->create($post->forum_thread->entity_type);
        if (! $resolver) {
            abort(400, 'A context cannot be resolved for the specified entity type "'.$post->forum_thread->entity_type.'".');
        }

        $url = $resolver->resolve($post->forum_thread->entity).'?forum_post_id='.$id;
        return redirect($url);
    }

    /**
     * HTTP GET. Retrieves forum post's data for editing purposes.
     *           Caller must be authenticated.
     *
     * @param Request $request
     * @return response 200 on success
     */
    public function edit(Request $request, int $id)
    {
        $post = ForumPost::findOrFail($id);
        
        if (! $this->userCanAccess($request->user(), $post)) {
            return response(null, 401);
        }

        return $post;
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
        $this->validate($request, [
            'comments'            => 'required|string',
            'parent_form_post_id' => 'sometimes|numeric|exists:forum_posts,id'
        ]);

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
   
    /**
     * HTTP PUT. Updates an existing forum post.
     *           Caller must be authenticated.
     *
     * @param Request $request
     * @return response 200 on success
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'comments' => 'required|string'
        ]);

        $account = $request->user();

        // fetch and update the post
        $post = ForumPost::findOrFail($id);
        if (! $this->userCanAccess($account, $post)) {
            return response(null, 401);
        }

        $post->content = $request->input('comments');
        $post->save();

        // update the thread's information
        $thread = $post->forum_thread;
        $thread->updated_at = $post->updated_at;
        $thread->account_id = $post->account_id;
        $thread->save();

        // Register an audit trail
        event(new ForumPostEdited($post, $post->account_id));

        return response(null, 200);
    }

    /**
     * HTTP DELETE. Deletes a forum post.
     *              Caller must be authenticated.
     *
     * @param Request $request
     * @return response 204 on success
     */
    public function destroy(Request $request, int $id)
    {
        $post = ForumPost::findOrFail($id);
        if (! $this->userCanAccess($request->user(), $post)) {
            return response(null, 401);
        }

        // hide the post (does not permanently delete it)
        $related = ForumPost::where('parent_forum_post_id', $post->id)->count();
        if ($related > 0) {
            $post->is_deleted = 1;
            $post->is_hidden = 0;
        } else {
            $post->is_deleted = 1;
            $post->is_hidden = 1;
        }

        $post->save();

        // update the thread
        $thread = $post->forum_thread;

        // reassign the 'latest' contributor to the thread
        if (! $thread->number_of_posts) {
            $thread->account_id = null;
        } else {
            $lastAccount = $thread->forum_posts()->where([
                ['is_deleted', 0],
                ['is_hidden', 0]
            ])
            ->orderBy('id', 'desc')
            ->first();

            $thread->account_id = $lastAccount ? $lastAccount->id : null;
        }

        // reduce number of likes and post counter
        $thread->number_of_posts = max(0, $thread->number_of_posts - 1);
        if ($post->number_of_likes > 0) {
            $thread->number_of_likes = max(0, $thread->number_of_likes - $post->number_of_likes);
        }

        $thread->save();

        return response(null, 204);
    }

    /**
     * HTTP POST. Likes a forum post.
     *            Caller must be authenticated.
     *
     * @param Request $request
     * @return response 201 or 204 on success
     */
    public function storeLike(Request $request, int $id)
    {
        $post = ForumPost::findOrFail($id);

        $userId = $request->user()->id;
        $statusCode = 204; // OK, but no action

        if (ForumPostLike::forPost($id, $userId)->count() < 1) {
            ForumPostLike::create([
                'account_id'    => $userId,
                'forum_post_id' => $id
            ]);

            $post->number_of_likes += 1;
            $post->save();

            $thread = $post->forum_thread;
            $thread->number_of_likes += 1;
            $thread->save();

            // Register an audit trail
            event(new ForumPostLikeCreated($post, $userId));

            $statusCode = 201; // OK, like saved
        }

        return response(null, $statusCode);
    }

    /**
     * HTTP DELETE. Un-like a forum post. This is not the same thing as disliking a post.
     *              Caller must be authenticated.
     *
     * @param Request $request
     * @return response 201 or 204 on success
     */
    public function destroyLike(Request $request, int $id)
    {
        $post = ForumPost::findOrFail($id);
        $userId = $request->user()->id;
        $statusCode = 204;

        $change = ForumPostLike::forPost($id, $userId)->delete();
        if ($change > 0) {
            $post->number_of_likes -= $change;
            $post->save();

            $thread = $post->forum_thread;
            $thread->number_of_likes -= $change;
            $thread->save();

            $statusCode = 201;
        }

        return response(null, $statusCode);
    }

    /**
     * Gets an existing forum thread or creates a new instance. A new of the thread
     * is not saved to the database, and must therefore be saved before it can be referenced
     * by a ForumPost entity.
     *
     * @param Request $request
     * @return \App\Models\ForumThread
     */
    private function getOrNewForumThread(Request $request) 
    {
        $this->validate($request, [
            'morph'     => 'required|string',
            'entity_id' => 'required|numeric',
            'subject'   => 'sometimes|required|string'
        ]);

        $morph = $request->input('morph');
        $entityId = intval($request->input('entity_id'));
        
        $resolver = $this->_contextFactory->create($morph);
        if (! $resolver) {
            abort(400, 'The entity '.$morph.' is not supported as it lacks a context '.
                       'implementation for '.$entityName.'.');
        }
        
        $thread = ForumThread::firstOrNew([
            'entity_type' => $morph,
            'entity_id'   => $entityId
        ]);

        if (! $thread->id) {
            $entityName = Morphs::getMorphedModel($morph);
            if (! $entityName) {
                abort(400, 'Entity '.$morph.' does not exist.');
            }

            $entity = resolve($entityName)->findOrFail($entityId);
            if (! $entity) {
                abort(400, 'Entity '.$morph.' with the ID '.$entityId.' does not exist.');
            }

            // Compose a default subject for the thread
            $subject = $request->has('subject')
                ? $request->input('subject') : '';
            if (empty($subject)) {
                $subject = $resolver->getName($entity);
            }
            $thread->subject = $subject;
        }

        if (! $resolver->available($thread, $request->user())) {
            abort(403);
        }

        return $thread;
    }

    private function userCanAccess($user, $post) 
    {
        return $user->isAdministrator() || 
               $post->account_id !== $user->id;
    }
}
