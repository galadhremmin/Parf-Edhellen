<?php

namespace App\Http\Controllers\Resources;

use App\Adapters\DiscussAdapter;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Discuss\ContextFactory;
use App\Models\Account;
use App\Repositories\DiscussRepository;
use App\Repositories\StatisticsRepository;
use App\Repositories\ValueObjects\ForumThreadFilterValue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DiscussController extends Controller
{
    protected ContextFactory $_contextFactory;

    protected DiscussAdapter $_discussAdapter;

    protected DiscussRepository $_discussRepository;

    protected StatisticsRepository $_statisticsRepository;

    private LinkHelper $_linkHelper;

    public function __construct(
        DiscussAdapter $discussAdapter,
        ContextFactory $contextFactory,
        DiscussRepository $discussRepository,
        StatisticsRepository $statisticsRepository,
        LinkHelper $linkHelper)
    {
        $this->_discussAdapter = $discussAdapter;
        $this->_discussRepository = $discussRepository;
        $this->_contextFactory = $contextFactory;
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
        $accounts = $this->_discussAdapter->adaptAccountsPerForumGroup(
            $this->_discussRepository->getAccountsInGroup($groups->getGroups())
        );

        $model = $groups->getAllValues() + [
            'accounts_in_group' => $accounts,
        ];

        return view('discuss.groups', $model);
    }

    public function group(Request $request, int $id)
    {
        $currentPage = max(0, intval($request->input('offset')));
        $filterNames = $request->input('filters') ?: [];

        if (! is_array($filterNames)) {
            $filterNames = [$filterNames];
        }

        try {
            $group = $this->_discussRepository->getGroup($id);
            $args = new ForumThreadFilterValue([
                'forum_group' => $group,
                'account' => $request->user(),
                'page_number' => $currentPage,
                'filter_names' => $filterNames,
            ]);
            $threads = $this->_discussRepository->getThreadDataInGroup($args);
            $this->_discussAdapter->adaptThreads($threads->getThreads());

            return view('discuss.group', $threads->getAllValues() + [
                'user' => $request->user(),
            ]);
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
            'group' => $this->_discussRepository->getGroup($groupId),
        ];
        $threadData = $this->_discussRepository->getThreadData($id);
        $this->_discussAdapter->adaptForumThread($threadData->getThread());

        $postData = $this->_discussRepository->getPostDataInThread($threadData->getThread(), $request->user(), 'asc', $currentPage, $forumPostId);
        $this->_discussAdapter->adaptPosts($postData->getPosts());

        $model = $threadData->getAllValues() + $groupData + [
            'preloadedPosts' => $postData->getAllValues(),
        ];

        return view('discuss.thread', $model);
    }

    public function topMembers(Request $request)
    {
        $cacheTtlInMinutes = 30 * 60;
        $data = Cache::remember('discuss.top-members', $cacheTtlInMinutes, function () use ($cacheTtlInMinutes) {
            return array_merge(
                $this->_statisticsRepository->getContributors(),
                [
                    'created_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addMinutes($cacheTtlInMinutes),
                ]
            );
        });

        return view('discuss.member-top-list', ['data' => $data]);
    }

    public function allMembers(Request $request)
    {
        $members = Account::orderBy('nickname', 'asc')
            ->where('is_deleted', false)
            ->paginate(30);

        return view('discuss.member-all-list', ['members' => $members]);
    }

    public function create(Request $request)
    {
        return view('discuss.create');
    }
}
