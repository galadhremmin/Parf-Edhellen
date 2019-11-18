<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth
};

use App\Adapters\{
    BookAdapter,
    DiscussAdapter
};
use App\Repositories\StatisticsRepository;
use App\Helpers\{
    MarkdownParser,
    StorageHelper
};
use App\Models\{ 
    Account, 
    ForumPost,
    Gloss,
    Sentence
};

class AuthorController extends Controller
{
    protected $_bookAdapter;
    protected $_discussAdapter;
    protected $_statisticsRepository;
    protected $_storageHelper;

    public function __construct(BookAdapter $bookAdapter, DiscussAdapter $discussAdapter, 
        StatisticsRepository $statisticsRepository, StorageHelper $storageHelper)
    {
        $this->_bookAdapter          = $bookAdapter;
        $this->_discussAdapter       = $discussAdapter;
        $this->_statisticsRepository = $statisticsRepository;
        $this->_storageHelper        = $storageHelper;
    }

    public function index(Request $request, int $id = null, $nickname = '')
    {
        $author  = $this->getAccount($request, $id);
        $profile = '';
        $stats   = null;

        if ($author) {
            $markdownParser = new MarkdownParser();

            $profile = $markdownParser->parse($author->profile ?? '');
            $stats   = $this->_statisticsRepository->getStatisticsForAccount($author);
        }

        return view('author.profile', [
            'author'  => $author,
            'profile' => $profile,
            'stats'   => $stats
        ]);
    }

    public function glosses(Request $request, int $id = null)
    {
        $author = $this->getAccount($request, $id);
        $entities = Gloss::active()
            ->forAccount($id)
            ->with('word', 'sense.word', 'language', 'gloss_group', 'translations')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        $glossary = $entities->map(function ($gloss) {
            $adapted = $this->_bookAdapter->adaptGloss($gloss);
            $adapted->sense = $gloss->sense->word->word;
            return $adapted;
        });
        
        return view('author.list-gloss', [
            'glossary' => $glossary,
            'author'  => $author
        ]);
    }

    public function sentences(Request $request, int $id = null)
    {
        $author = $this->getAccount($request, $id);
        $sentences = Sentence::approved()
            ->forAccount($id)
            ->with('language')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();
        
        return view('author.list-sentence', [
            'sentences' => $sentences,
            'author'    => $author
        ]);
    }

    public function posts(Request $request, int $id)
    {
        $noOfPosts = ForumPost::forAccount($id)->count();
        $pageSize = 10;
        $page = $request->has('page') 
            ? intval($request->input('page')) 
            : 0;

        $posts = ForumPost::forAccount($id)
            ->with('forum_thread')
            ->where([
                ['is_deleted', 0],
                ['is_hidden', 0]
            ])
            ->orderBy('id', 'desc')
            ->skip($page * $pageSize)
            ->take($pageSize)
            ->get();

        $adapted = $this->_discussAdapter->adaptForTimeline($posts);
        $author = Account::findOrFail($id);

        return view('author.list-post', [
            'posts'     => $adapted,
            'noOfPosts' => $noOfPosts,
            'noOfPages' => ceil($noOfPosts / $pageSize),
            'page'      => $page,
            'author'    => $author
        ]);
    }

    public function edit(Request $request, int $id = 0)
    {
        $author = $this->getAccount($request, $id);

        return view('author.edit-profile', [
            'author' => $author
        ]);
    }

    private function getAccount(Request $request, $id)
    {
        if (! is_numeric($id) || ! $id) {
            $id = 0;

            if (Auth::check()) {
                $user = $request->user();
                $id = $user->id;
            }
        }

        $account = Account::findOrFail($id);
        if ($account->has_avatar) {
            $account->avatar_path = $this->_storageHelper->accountAvatar($account, false /* = _null_ if none exists */);
        }

        return $account;
    }
}
