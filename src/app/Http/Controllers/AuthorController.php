<?php

namespace App\Http\Controllers;
use App\Models\Account;
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

    public function edit(Request $request, $id = null)
    {
        $author = $this->getAccount($request, $id);

        return view('author.edit-profile', [
            'author' => $author
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $author = $this->getAccount($request, $id);
        if ($author === null) {
            return response('', 404);
        }

        $this->validate($request, [
            'nickname' => 'bail|required|unique:account,nickname,' . $author->id . ',id|min:3|max:32'
        ]);

        $author->nickname = $request->input('nickname');
        $author->tengwar  = $request->input('tengwar');
        $author->profile  = $request->input('profile');
        $author->save();

        return redirect()->route('author.my-profile');
    }

    private function getAccount(Request $request, $id)
    {
        if (!is_numeric($id)) {

            if (!Auth::check()) {
                return null;
            }

            $user = $request->user();
            $id = $user->id;
        }

        return Account::find($id);
    }
}
