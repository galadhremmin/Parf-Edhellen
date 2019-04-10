<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\Initialization\Morphs;
use App\Http\Discuss\ContextFactory;
use App\Helpers\LinkHelper;
use App\Repositories\{
    DiscussRepository,
    MailSettingRepository
};
use App\Models\{ 
    Account,
    ForumGroup,
    ForumPost, 
    ForumPostLike, 
    ForumThread
};
use App\Events\{
    ForumPostCreated,
    ForumPostEdited,
    ForumPostLikeCreated
};

class ForumApiController extends Controller 
{
    protected $_discussRepository;
    protected $_mailSettings;
    protected $_contextFactory;

    public function __construct(DiscussRepository $discussRepository,
        MailSettingRepository $mailSettingsRepository, 
        ContextFactory $contextFactory)
    {
        $this->_discussRepository = $discussRepository;
        $this->_mailSettings      = $mailSettingsRepository;
        $this->_contextFactory    = $contextFactory;
    }

    public function index(Request $request)
    {
        return $this->_discussRepository->getGroups();
    }

    public function show(Request $request, int $id)
    {
        return [
            'group' => $this->_discussRepository->getGroup($id)
        ];
    }

    public function threadForPost(Request $request, int $id)
    {
        $post = ForumPost::where('id', $id)->select('forum_thread_id')->first();
        if ($post === null) {
            abort(404, 'The post you are looking for does not exist');
        }

        $linker = new LinkHelper();
        $path = $linker->forumThread(
            $post->forum_thread->forum_group_id, $post->forum_thread->forum_group->name,
            $post->forum_thread_id, $post->forum_thread->normalized_subject,
            $id
        );

        return redirect($path);
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
            return response(null, 403);
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
            return response(null, 403);
        }

        $post->content = $request->input('comments');
        $post->save();

        // update the thread's information
        $thread = $post->forum_thread;
        $thread->updated_at = $post->updated_at;
        $thread->account_id = $post->account_id;
        $thread->timestamps = false;
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
            return response(null, 403);
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
        if ($thread->number_of_posts < 1) {
            $thread->account_id = null;
        } else {
            $lastAccount = $thread->forum_posts()->where([
                ['is_deleted', 0],
                ['is_hidden', 0]
            ])
            ->orderBy('id', 'desc')
            ->first();

            $thread->account_id = $lastAccount ? $lastAccount->account_id : null;
        }

        // reduce number of likes and post counter
        $thread->number_of_posts = max(0, $thread->number_of_posts - 1);
        if ($post->number_of_likes > 0) {
            $thread->number_of_likes = max(0, $thread->number_of_likes - $post->number_of_likes);
        }

        $thread->timestamps = false;
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
            $thread->timestamps = false;
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
            $thread->timestamps = false;
            $thread->save();

            $statusCode = 201;
        }

        return response(null, $statusCode);
    }

    /**
     * HTTP GET. Gets subscription status for a specific forum thread.
     * @param Request $request
     * @param int $id The ID of the thread.
     * @return array Associtative array with one boolean key `subscribed`
     */
    public function getSubscription(Request $request, int $id) 
    {
        $userId = $request->user()->id;
        $override = null;

        $thread = ForumThread::find($id);
        if ($thread) {
            $override = $this->_mailSettings->getOverride($userId, $thread->entity);
        }

        return [
            'subscribed' => ($override && ! $override->disabled)
        ];
    }

    /**
     * HTTP POST. Subscribes to a specific forum thread.
     * @param Request $request
     * @param int $id The ID of the thread.
     * @return array Associtative array with one boolean key `subscribed`
     */
    public function storeSubscription(Request $request, int $id)
    {
        $userId = $request->user()->id;
        return $this->saveSubscription($id, $userId, true);
    }

    /**
     * HTTP DELETE. Unsubscribes from a specific forum thread.
     * @param Request $request
     * @param int $id The ID of the thread.
     * @param int $userId 
     * @return array Associtative array with one boolean key `subscribed`
     */
    public function destroySubscription(Request $request, int $id)
    {
        $userId = $request->user()->id;
        return $this->saveSubscription($id, $userId, false);
    }

    /**
     * Subscribes or unsubscribes from a specific forum thread.
     * @param int $id The ID of the thread.
     * @param bool $subscribed 
     * @return array Associtative array with one boolean key `subscribed`
     */
    private function saveSubscription(int $id, int $userId, bool $subscribed)
    {
        $thread = ForumThread::findOrFail($id);
        $subscribed = $this->_mailSettings->setNotifications($userId, $thread->entity, $subscribed);
        return [
            'subscribed' => $subscribed
        ];
    }

    /**
     * HTTP GET. Gets whether the specified thread is 'sticky' (essentially, always put on top)
     * @param Request $request
     * @param int $id The ID of the thread.
     * @return array Associtative array with one boolean key `sticky`
     */
    public function getSticky(Request $request, int $id)
    {
        $thread = ForumThread::where('id', $id)
            ->select('is_sticky')
            ->firstOrFail();
        
        return [
            'sticky' => $thread->is_sticky
        ];
    }

    /**
     * HTTP POST. Makes the specified thread 'sticky' (essentially, always putting it on top)
     * @param Request $request
     * @param int $id The ID of the thread.
     * @return array Associtative array with one boolean key `sticky`
     */
    public function storeSticky(Request $request, int $id)
    {
        return $this->saveSticky($id, true);
    }

    /**
     * HTTP DELETE. Converts a sticky thread into a normal thread.
     * @param Request $request
     * @param int $id The ID of the thread.
     * @return array Associtative array with one boolean key `sticky`
     */
    public function destroySticky(Request $request, int $id)
    {
        return $this->saveSticky($id, false);
    }

    /**
     * Saves a thread's stickiness.
     * @param int $id The ID of the thread.
     * @param bool $sticky 
     * @return array Associtative array with one boolean key `sticky`
     */
    private function saveSticky(int $id, bool $sticky)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->is_sticky = $sticky;
        $thread->save();

        return [
            'sticky' => $thread->is_sticky
        ];
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

        if (! $resolver->available($thread->entity_id, $request->user())) {
            abort(403);
        }

        return $thread;
    }

    private function userCanAccess($user, $post) 
    {
        return $user->isAdministrator() || 
               $post->account_id === $user->id;
    }
}
