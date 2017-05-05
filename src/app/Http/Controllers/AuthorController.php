<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Account;
use App\Repositories\StatisticsRepository;
use App\Helpers\MarkdownParser;

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

        $filePath = 'avatars/'.$author->id.'.png';
        return view('author.profile', [
            'author'  => $author,
            'profile' => $profile,
            'stats'   => $stats,
            'avatar'  => Storage::disk('local')->exists('public/'.$filePath) 
                ? Storage::url($filePath)
                : null
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
            'nickname' => 'bail|required|unique:accounts,nickname,' . $author->id . ',id|min:3|max:32',
            'avatar'   => 'image'
        ]);
        
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            $valid = $file->isValid();
            $size  = false;
            if ($valid) {
                $size = getimagesize($file->path());
                $valid = $size !== false && $size[0] > 0 && $size[1] > 0;
            }

            if ($valid) {
                list($width, $height) = $size;

                $factor = config('ed.avatar_size') / max($width, $height);

                $newWidth  = ceil($width * $factor);
                $newHeight = ceil($height * $factor);

                $avatar   = imagecreatetruecolor($newWidth, $newHeight);
                $original = imagecreatefromstring(file_get_contents($file->path()));

                imagecopyresized($avatar, $original, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                ob_start();
                imagepng($avatar);
                $avatarAsString = ob_get_clean();

                Storage::disk('local')->put('public/avatars/'.$author->id.'.png', $avatarAsString);

                imagedestroy($original);
                imagedestroy($avatar);

                $author->has_avatar = true;
            }

            unlink($file->path());
        }

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
