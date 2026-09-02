<?php

namespace App\Http\Controllers;

use App\Adapters\BookAdapter;
use App\Adapters\DiscussAdapter;
use App\Adapters\WordListAdapter;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Interfaces\IMarkdownParser;
use App\Models\Account;
use App\Models\ForumPost;
use App\Models\LexicalEntry;
use App\Models\Sentence;
use App\Models\WordList;
use App\Repositories\StatisticsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthorController extends Controller
{
    /**
     * Word lists shown on a profile. A person with a great many of them gets the most recently
     * changed ones; the rest are a click away on the list itself.
     */
    private const NUMBER_OF_WORD_LISTS = 8;

    /**
     * Words previewed under each list, enough to convey what the list is about.
     */
    private const NUMBER_OF_PREVIEW_WORDS = 6;

    protected BookAdapter $_bookAdapter;

    protected DiscussAdapter $_discussAdapter;

    protected StatisticsRepository $_statisticsRepository;

    protected StorageHelper $_storageHelper;

    protected IMarkdownParser $_markdownParser;

    protected WordListAdapter $_wordListAdapter;

    public function __construct(BookAdapter $bookAdapter, DiscussAdapter $discussAdapter,
        StatisticsRepository $statisticsRepository, StorageHelper $storageHelper,
        IMarkdownParser $markdownParser, WordListAdapter $wordListAdapter)
    {
        $this->_bookAdapter = $bookAdapter;
        $this->_discussAdapter = $discussAdapter;
        $this->_statisticsRepository = $statisticsRepository;
        $this->_storageHelper = $storageHelper;
        $this->_markdownParser = $markdownParser;
        $this->_wordListAdapter = $wordListAdapter;
    }

    public function index(Request $request, ?int $id = null, $nickname = '')
    {
        $author = $this->getAccount($request, $id);
        $profile = '';
        $stats = null;
        $wordLists = [];

        if ($author) {
            $profile = $this->_markdownParser->parseMarkdown($author->profile ?? '');
            $stats = $this->_statisticsRepository->getStatisticsForAccount($author);
            $wordLists = $this->getPublicWordLists($author);
        }

        return view('author.profile', [
            'author' => $author,
            'profile' => $profile,
            'stats' => $stats,
            'wordLists' => $wordLists,
        ]);
    }

    /**
     * The word lists this person has chosen to publish, each with a handful of its words.
     *
     * Private lists are excluded regardless of who is looking, including the owner: the profile is a
     * public page, and a list shown there reads as published whether or not it is.
     */
    private function getPublicWordLists(Account $author): array
    {
        $wordLists = WordList::where('account_id', $author->id)
            ->where('is_public', 1)
            ->withCount('lexical_entries')
            ->with(['lexical_entries' => fn ($query) => $query
                ->select('lexical_entries.id', 'lexical_entries.word_id')
                ->orderBy('word_list_entries.order')
                ->limit(self::NUMBER_OF_PREVIEW_WORDS),
                'lexical_entries.word',
            ])
            ->orderBy('updated_at', 'desc')
            ->limit(self::NUMBER_OF_WORD_LISTS)
            ->get();

        return $wordLists->map(
            fn (WordList $wordList) => $this->_wordListAdapter->adaptPreview($wordList)
        )->all();
    }

    public function glosses(Request $request, ?int $id = null)
    {
        $author = $this->getAccount($request, $id);
        $entities = LexicalEntry::active()
            ->forAccount($id)
            ->with('word', 'sense.word', 'language', 'lexical_entry_group', 'glosses')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        $glossary = $entities->map(function ($lexicalEntry) {
            $adapted = $this->_bookAdapter->adaptLexicalEntry($lexicalEntry);
            $adapted->sense = $lexicalEntry->sense->word->word;

            return $adapted;
        });

        return view('author.list-gloss', [
            'glossary' => $glossary,
            'author' => $author,
        ]);
    }

    public function sentences(Request $request, ?int $id = null)
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
            'author' => $author,
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
                ['is_hidden', 0],
            ])
            ->orderBy('id', 'desc')
            ->skip($page * $pageSize)
            ->take($pageSize)
            ->get();

        $adapted = $this->_discussAdapter->adaptForTimeline($posts);
        $author = Account::findOrFail($id);

        return view('author.list-post', [
            'posts' => $adapted,
            'noOfPosts' => $noOfPosts,
            'noOfPages' => ceil($noOfPosts / $pageSize),
            'page' => $page,
            'author' => $author,
        ]);
    }

    public function edit(Request $request, int $id = 0)
    {
        $author = $this->getAccount($request, $id);

        return view('author.edit-profile', [
            'author' => $author,
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

        // If this account has been linked to a master account, forward the request to that account.
        // Linked accounts are only used for authentication.
        if ($account->master_account_id !== null) {
            $account = $account->master_account;
        }

        if ($account->has_avatar) {
            $account->avatar_path = $this->_storageHelper->accountAvatar($account, false /* = _null_ if none exists */);
        }

        return $account;
    }
}
