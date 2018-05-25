<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Cache; 

use App\Http\Controllers\Controller;
use App\Http\Discuss\ContextFactory;
use App\Adapters\DiscussAdapter;
use App\Models\Initialization\Morphs;
use App\Events\ForumPostCreated;
use App\Repositories\StatisticsRepository;
use App\Models\{
    Account,
    ForumDiscussion,
    ForumThread,
    ForumPost
};

class DiscussController extends Controller
{
    protected $_discussAdapter;
    protected $_contextFactory;
    protected $_statisticsRepository;

    public function __construct(DiscussAdapter $discussAdapter, ContextFactory $contextFactory,
        StatisticsRepository $statisticsRepository) 
    {
        $this->_discussAdapter       = $discussAdapter;
        $this->_contextFactory       = $contextFactory;
        $this->_statisticsRepository = $statisticsRepository;
    }

    public function index(Request $request)
    {
        $threads = ForumThread::where('number_of_posts', '>', 0)
            ->with('account')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $adapted = $this->_discussAdapter->adaptThreads($threads);
        return view('discuss.index', [
            'threads' => $adapted
        ]);
    }

    public function members(Request $request)
    {
        $cacheTtlInMinutes = 30;
        $data = Cache::remember('discuss.members', $cacheTtlInMinutes, function () use($cacheTtlInMinutes) {
            return array_merge(
                $this->_statisticsRepository->getContributors(),
                [ 
                    'created_at' => time(), 
                    'expires_at' => time() + $cacheTtlInMinutes * 60 
                ]
            );
        });
        
        return view('discuss.member-list', ['data' => $data]);
    }

    public function show(Request $request, int $id)
    {
        $thread = ForumThread::findOrFail($id);
        if ($thread->number_of_posts < 1) {
            abort(404, 'The thread you are looking for does not exist.');
        }

        $context = $this->_contextFactory->create($thread->entity_type);
        $user = $request->user();
        if (! $context->available($thread, $user)) {
            if (! $user) {
                throw new AuthenticationException;
            } 
            
            abort(403);
        }

        return view('discuss.show', [
            'thread'  => $thread,
            'context' => $context
        ]);
    }

    public function create(Request $request)
    {
        return view('discuss.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'subject' => 'required|string|min:3',
            'content' => 'required|string|min:3'
        ]);

        $userId = $request->user()->id;

        // Create a discussion which will be the entity associated with
        // the thread.
        $discussion = ForumDiscussion::create([
            'account_id' => $userId
        ]);
        $typeName = Morphs::getAlias($discussion);

        // Create a forum thread for the previously created discussion.
        $thread = ForumThread::create([
            'entity_type'     => $typeName,
            'entity_id'       => $discussion->id,
            'subject'         => $request->input('subject'),
            'account_id'      => $userId,
            'number_of_posts' => 1
        ]);

        // Create a post with the user's message content
        $post = ForumPost::create([
            'account_id'      => $userId,
            'forum_thread_id' => $thread->id,
            'content'         => $request->input('content')
        ]);

        event(new ForumPostCreated($post, $userId));

        return redirect()->route('discuss.show', ['id' => $thread->id]);
    }

    public function resolveThread(Request $request, int $id)
    {
        $discuss = ForumDiscussion::findOrFail($id);
        if ($discuss === null) {
            abort(404, 'The discussion does not exist.');
        }

        return redirect()->route('discuss.show', ['id' => $discuss->forum_thread->id]);
    }
}
