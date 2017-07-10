<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Models\{ ForumPost, ForumPostLike, ForumContext, Translation, Sentence };
use App\Http\Controllers\Controller;
use App\Helpers\{ StringHelper, MarkdownParser };

class ForumApiController extends Controller 
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $context = $this->getContext($request);
        if (! $context) {
            return response(null, 404);
        }

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

        $posts = ForumPost::where('context_id', $context['id'])
            ->with($loadingOptions)
            ->where([
                ['entity_id', $context['entity']->id],
                ['is_hidden', 0]
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $parser = new MarkdownParser();
        foreach ($posts as $post) {
            $post->content = $parser->parse($post->content);
        }

        return $posts;
    }

    /**
     * HTTP GET. Retrieves forum post's data for editing purposes.
     *           Caller must be authenticated.
     *
     * @param Request $request
     * @return response 201 on success
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

        $context = $this->getContext($request);
        if (! $context) {
            return response(null, 404);
        }

        $comments = $request->input('comments');

        $parentEntityId = null;
        if ($request->has('parent_form_post_id')) {
            $parentEntityId = $request->input('parent_form_post_id');
        }

        ForumPost::create([
            'context_id'          => $context['id'],
            'entity_id'           => $context['entity']->id,
            'account_id'          => $request->user()->id,
            'content'             => $comments,
            'parent_form_post_id' => $parentEntityId,
            'number_of_likes'     => 0
        ]);

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

        $post = ForumPost::findOrFail($id);
        if (! $this->userCanAccess($request->user(), $post)) {
            return response(null, 401);
        }

        $post->content = $request->input('comments');
        $post->save();

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
     * @return response 201 on success
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

            $statusCode = 201; // OK, like saved
        }

        return response(null, $statusCode);
    }

    /**
     * HTTP DELETE. Un-like a forum post. This is not the same thing as disliking a post.
     *              Caller must be authenticated.
     *
     * @param Request $request
     * @return response 201 on success
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

    private function getContext(Request $request) 
    {
        $this->validate($request, [
            'context'   => 'required|exists:forum_contexts,name',
            'entity_id' => 'required|numeric'
        ]);

        // Retrieve the context
        $context = ForumContext::where('name', $request->input('context'))
            ->select('id')
            ->firstOrFail();

        $id = intval($request->input('entity_id'));
        $entity = null;

        switch ($context->id) {
            case ForumContext::CONTEXT_TRANSLATION:
                $entity = Translation::active()
                    ->where('id', $id)
                    ->firstOrFail();
                break;
            
            case ForumContext::CONTEXT_SENTENCE:
                $entity = Sentence::findOrFail($id);
                break;

            default:
                return null;
        }

        return [
            'id'     => $context->id,
            'entity' => $entity
        ];
    }

    private function userCanAccess($user, $post) 
    {
        return $user->isAdministrator() || 
               $post->account_id !== $user->id;
    }
}