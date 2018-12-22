<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Cache; 
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Discuss\ContextFactory;
use App\Adapters\DiscussAdapter;
use App\Models\Initialization\Morphs;
use App\Events\ForumPostCreated;
use App\Repositories\StatisticsRepository;
use App\Helpers\{
    LinkHelper,
    StringHelper
};
use App\Models\{
    Account,
    ForumDiscussion,
    ForumGroup,
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
        return $this->groups($request);
    }

    public function groups(Request $request)
    {
        return view('discuss.groups', [
            'groups' => ForumGroup::orderBy('name')->get()
        ]);
    }

    public function group(Request $request, int $id)
    {
        $group = ForumGroup::findOrFail($id);
        $noOfThreadsPerPage = config('ed.forum_thread_resultset_max_length');
        $noOfPages = ceil(ForumThread::inGroup($id)->count() / $noOfThreadsPerPage);
        $currentPage = min($noOfPages - 1, max(0, intval($request->input('offset'))));

        $threads = ForumThread::inGroup($id)
            ->with('account')
            ->orderBy('is_sticky', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->skip($currentPage * $noOfThreadsPerPage)
            ->take($noOfThreadsPerPage)
            ->get();
        
        $pages = [];
        for ($i = 0; $i < $noOfPages; $i += 1) {
            $pages[$i] = $i + 1;
        }

        $adapted = $this->_discussAdapter->adaptThreads($threads);
        return view('discuss.group', [
            'group'   => $group,
            'threads' => $adapted,
            'pages'   => $pages,
            'currentPage' => $currentPage,
            'noOfPages' => $noOfPages
        ]);
    }

    public function topMembers(Request $request)
    {
        $cacheTtlInMinutes = 30;
        $data = Cache::remember('discuss.top-members', $cacheTtlInMinutes, function () use($cacheTtlInMinutes) {
            return array_merge(
                $this->_statisticsRepository->getContributors(),
                [ 
                    'created_at' => Carbon::now(), 
                    'expires_at' => Carbon::now()->addMinutes($cacheTtlInMinutes) 
                ]
            );
        });
        
        return view('discuss.member-top-list', ['data' => $data]);
    }

    public function allMembers(Request $request)
    {
        $members = Account::orderBy('nickname', 'asc')
            ->paginate(30);

        return view('discuss.member-all-list', ['members' => $members]);
    }

    public function show(Request $request, int $groupId, string $groupSlug, int $id)
    {
        $group  = ForumGroup::findOrFail($groupId);
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

        return view('discuss.thread', [
            'group'   => $group,
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
            'entity_type'        => $typeName,
            'entity_id'          => $discussion->id,
            'subject'            => $request->input('subject'),
            'normalized_subject' => StringHelper::normalize($request->input('subject')),
            'account_id'         => $userId,
            'number_of_posts'    => 1
        ]);

        // Create a post with the user's message content
        $post = ForumPost::create([
            'account_id'      => $userId,
            'forum_thread_id' => $thread->id,
            'content'         => $request->input('content')
        ]);

        event(new ForumPostCreated($post, $userId));

        $linker = new LinkHelper();
        return redirect($linker->forumThread($thread->id, $thread->normalized_subject));
    }

    public function resolveThread(Request $request, int $id)
    {
        $discuss = ForumDiscussion::findOrFail($id);
        if ($discuss === null) {
            abort(404, 'The discussion does not exist.');
        }

        $linker = new LinkHelper();
        return redirect($linker->forumThread($discuss->forum_thread->id, $discuss->forum_thread->normalized_subject));
    }
}
