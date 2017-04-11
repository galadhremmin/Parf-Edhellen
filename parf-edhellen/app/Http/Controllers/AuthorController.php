<?php

namespace App\Http\Controllers;
use App\Models\Author;
use App\Repositories\StatisticsRepository;
use Illuminate\Http\Request;
use App\Helpers\MarkdownParser;
use Illuminate\Support\Facades\Auth;

class AuthorController extends Controller
{
    private $_statisticsRepository;

    public function __construct(StatisticsRepository $statisticsRepository)
    {
        $this->_statisticsRepository = $statisticsRepository;
    }

    public function index(Request $request, $id = null, $nickname = '')
    {
        if (!is_numeric($id)) {
            $id = $this->getUserId($request);
        }

        $author  = Author::find($id);
        $profile = '';
        $stats   = null;

        if ($author) {
            $markdownParser = new MarkdownParser();

            $profile = $markdownParser->parse($author->Profile ?? '');
            $stats   = $this->_statisticsRepository->getStatisticsForAuthor($author);
        }

        return view('author.profile', [
            'author'  => $author,
            'profile' => $profile,
            'stats'   => $stats
        ]);
    }

    public function edit(Request $request, $id = 0)
    {
        if (!is_numeric($id)) {
            $id = $this->getUserId($request);
        }

        $author = Author::find($id);

        return view('author.edit-profile', [
            'author' => $author
        ]);
    }

    private function getUserId(Request $request) {
        if (!Auth::check()) {
            return 0;
        }

        $user = $request->user();
        return $user->AccountID;
    }
}
