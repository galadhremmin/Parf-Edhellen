<?php

namespace App\Http\Controllers;
use App\Models\Author;
use App\Repositories\StatisticsRepository;
use Illuminate\Http\Request;
use App\Helpers\MarkdownParser;

class AuthorController extends Controller
{
    private $_statisticsRepository;

    public function __construct(StatisticsRepository $statisticsRepository)
    {
        $this->_statisticsRepository = $statisticsRepository;
    }

    public function index(Request $request, int $id, string $nickname)
    {
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

    public function edit() 
    {
        $author = []; // Author::find($id);

        return view('author.edit-profile', [
            'author' => $author
        ]);
    }
}
