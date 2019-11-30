<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Cache;
use Log;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Discuss\ContextFactory;
use App\Http\Controllers\Traits\CanAdaptDiscuss;
use App\Adapters\DiscussAdapter;
use App\Helpers\LinkHelper;
use App\Repositories\{
    DiscussRepository,
    StatisticsRepository
};
use App\Models\{
    Account
};

class DiscussController extends Controller
{
    use CanAdaptDiscuss {
        CanAdaptDiscuss::__construct as setupDiscussAdapter;
    }

    protected $_contextFactory;
    protected $_discussRepository;
    protected $_statisticsRepository;
    private $_linkHelper;

    public function __construct(
        DiscussAdapter $discussAdapter,
        ContextFactory $contextFactory,
        DiscussRepository $discussRepository,
        StatisticsRepository $statisticsRepository,
        LinkHelper $linkHelper)
    {
        $this->setupDiscussAdapter($discussAdapter);
        $this->_discussRepository    = $discussRepository;
        $this->_contextFactory       = $contextFactory;
        $this->_statisticsRepository = $statisticsRepository;
        $this->_linkHelper = $linkHelper;
    }

    public function index(Request $request)
    {
        return $this->groups($request);
    }

    public function groups(Request $request)
    {
        $groups = $this->_discussRepository->getGroups();
        return view('discuss.groups', $groups->getAllValues());
    }

    public function group(Request $request, int $id)
    {
        $currentPage = max(0, intval($request->input('offset')));

        try {
            $group = $this->_discussRepository->getGroup($id);
            $model = $this->adaptForumThreadsInGroup(
                $this->_discussRepository->getThreadDataInGroup($group, $request->user(), $currentPage)
            );
        
            return view('discuss.group', $model->getAllValues());
        } catch (ModelNotFoundException $ex) {
            // unfortunately, before groups were a thing, the path pattern was identical to threads
            // so implement a graceful fallback before giving up:
            $threadData = $this->_discussRepository->getThreadData($id);
            $thread = $threadData->getThread();
            $path = $this->_linkHelper->forumThread(
                $thread->forum_group_id, $thread->forum_group->name,
                $id, $thread->normalized_subject
            );
            return redirect($path);
        }
    }

    public function show(Request $request, int $groupId, string $groupSlug, int $id)
    {
        $currentPage = max(0, intval($request->get('offset')));
        $forumPostId = intval($request->get('forum_post_id'));

        $groupData = [
            'group' => $this->_discussRepository->getGroup($groupId)
        ];
        $threadData = $this->adaptForumThread(
            $this->_discussRepository->getThreadData($id)
        );
        $postData = $this->adaptForumPostsInThread(
            $this->_discussRepository->getPostDataInThread($threadData->getThread(), $request->user(), 'asc', $currentPage, $forumPostId)
        );

        return view('discuss.thread', $threadData->getAllValues() + $groupData + [
            'preloadedPosts' => $postData->getAllValues()
        ]);
    }

    public function topMembers(Request $request)
    {
        $cacheTtlInMinutes = 30 * 60;
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

    public function create(Request $request)
    {
        return view('discuss.create');
    }
}
