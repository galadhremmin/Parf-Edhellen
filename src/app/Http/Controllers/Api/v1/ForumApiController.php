<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\ForumRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Models\Initialization\Morphs;
use App\Http\RouteResolving\RouteResolverFactory;
use App\Models\{ 
    Account,
    AuditTrail,
    Contribution,
    ForumPost, 
    ForumPostLike, 
    ForumThread, 
    Translation, 
    Sentence 
};
use App\Helpers\{ 
    MarkdownParser, 
    StringHelper 
};

class ForumApiController extends Controller 
{
    protected $_auditTrail;
    protected $_repository;
    protected $_routeResolverFactory;

    public function __construct(IAuditTrailRepository $auditTrail, ForumRepository $repository, RouteResolverFactory $routeResolverFactory)
    {
        $this->_auditTrail           = $auditTrail;
        $this->_repository           = $repository;
        $this->_routeResolverFactory = $routeResolverFactory;
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
                : ($ascending ? 0 : PHP_INT_MAX);

            $posts = $thread->forum_posts()
                ->with($loadingOptions)
                ->where([
                    ['is_hidden', 0],
                    ['id', $ascending ? '>' : '<', $majorId]
                ])
                ->orderBy('id', $direction)
                ->take($maxLength)
                ->get();

            $parser = new MarkdownParser();
            foreach ($posts as $post) {

                // Determine the major ID depending on the order of the items
                if (($ascending && $majorId < $post->id) ||
                    (! $ascending && $majorId > $post->id)) {
                    $majorId = $post->id;
                }

                $post->content = $parser->parse($post->content);
            }
        } else {
            $posts = [];
            $majorId = 0;
        }

        return [
            'posts'    => $posts,
            'major_id' => $majorId
        ];
    }

    public function show(Request $request, int $id)
    {
        $post = ForumPost::findOrFail($id);

        $resolver = $this->_routeResolverFactory->create($post->forum_thread->entity_type);
        if (! $resolver) {
            abort(400, 'A resolver does not exist for the specified entity type "'.$post->forum_thread->entity_type.'".');
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
        if (! $thread->id) {
            $thread->save();
        }

        $account = $request->user();
        $post = ForumPost::create([
            'forum_thread_id'     => $thread->id,
            'account_id'          => $account->id,
            'content'             => $comments,
            'parent_form_post_id' => $parentEntityId,
            'number_of_likes'     => 0
        ]);

        // Register an audit trail
        $this->_auditTrail->store(AuditTrail::ACTION_COMMENT_ADD, $post, /* user id = */ 0, $thread->roles !== null);

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
        $post = ForumPost::findOrFail($id);
        if (! $this->userCanAccess($account, $post)) {
            return response(null, 401);
        }

        $post->content = $request->input('comments');
        $post->save();

        // Register an audit trail
        $this->_auditTrail->store(AuditTrail::ACTION_COMMENT_EDIT, $post, /* user id = */ 0, $post->forum_thread->roles !== null);

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

        $related = ForumPost::where('parent_forum_post_id', $post->id)->count();
        if ($related > 0) {
            $post->is_deleted = 1;
            $post->is_hidden = 0;
        } else {
            $post->is_deleted = 1;
            $post->is_hidden = 1;
        }

        $post->save();
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

            // Register an audit trail
            $this->_auditTrail->store(AuditTrail::ACTION_COMMENT_LIKE, $post, $userId, $post->forum_thread->roles !== null);

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
        
        $thread = ForumThread::where([
            ['entity_type', $morph],
            ['entity_id', $entityId]
        ])->firstOrNew([
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

            $resolver = $this->_routeResolverFactory->create($morph);
            if (! $resolver) {
                abort(400, 'The entity '.$morph.' is not supported as it lacks a IRouteResolver implementation for '.$entityName.'.');
            }

            $subject = $request->has('subject')
                ? $request->input('subject') : '';
            
            if (empty($subject)) {
                $subject = $resolver->getName($entity);
            }

            $roles = $resolver->getRoles();
            $user = $request->user();
            $ok = (count($roles) === 0);

            if ($user !== null) {
                foreach ($roles as $role) {
                    if ($user->memberOf($role)) {
                        $ok = true;
                        break;
                    }
                }
            }

            if (! $ok) {
                abort(400, 'User '.$user->id.' is not authorized to create threads for '.$morph.'.');
            }

            $thread->subject = $subject;
            $thread->roles   = count($roles) ? implode(',', $roles) : null;
        }

        return $thread;
    }

    private function userCanAccess($user, $post) 
    {
        return $user->isAdministrator() || 
               $post->account_id !== $user->id;
    }
}
