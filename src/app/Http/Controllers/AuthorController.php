<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{
    Auth,
    Storage
};

use App\Adapters\DiscussAdapter;
use App\Repositories\StatisticsRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Helpers\{
    MarkdownParser,
    StorageHelper
};

use App\Models\{ 
    Account, 
    AuditTrail,
    ForumPost,
    Translation,
    Sentence
};

class AuthorController extends Controller
{
    protected $_auditTrail;
    protected $_discussAdapter;
    protected $_statisticsRepository;
    protected $_storageHelper;

    public function __construct(IAuditTrailRepository $auditTrail, DiscussAdapter $discussAdapter, 
        StatisticsRepository $statisticsRepository, StorageHelper $storageHelper)
    {
        $this->_auditTrail           = $auditTrail;
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
            'stats'   => $stats,
            'avatar'  => $this->_storageHelper->accountAvatar($author, false /* = _null_ if none exists */)
        ]);
    }

    public function translations(Request $request, int $id = null)
    {
        $author = Account::findOrFail($id);
        $translations = Translation::active()
            ->forAccount($id)
            ->with('word', 'sense.word', 'language', 'translation_group')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();
        
        return view('author.list-translation', [
            'translations' => $translations,
            'author'       => $author
        ]);
    }

    public function sentences(Request $request, int $id = null)
    {
        $author = Account::findOrFail($id);
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

        return view('author.list-posts', [
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

    public function update(Request $request, $id = null)
    {
        $author = $this->getAccount($request, $id);
        if ($author === null) {
            return response('', 404);
        }

        $this->validate($request, [
            'nickname' => 'bail|required|unique:accounts,nickname,' . $author->id . ',id|min:3|max:32',
            'avatar'   => 'sometimes|image'
        ]);
        
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $this->uploadProfile($author, $file);
        }

        $author->nickname = $request->input('nickname');
        $author->tengwar  = $request->input('tengwar');
        $author->profile  = $request->input('profile');

        $changed = $author->isDirty();
        if ($changed) {
            $author->save();

            // Register an audit trail for the changed profile
            $this->_auditTrail->store(AuditTrail::ACTION_PROFILE_EDIT, $author);
        }

        return redirect()->route('author.my-profile');
    }

    private function uploadProfile(Account $author, UploadedFile $file)
    {
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

            try {
                // Read the original image into memory, and scale it to its destination size + 1.
                // The extra 'bleed' is used to remedy a scaling bug in PHP which results in a black
                // border in the lower as well as the right corner of the image.
                $original = imagecreatefromstring(file_get_contents($file->path()));
                $avatar   = imagescale($original, $newWidth + 1, $newHeight + 1, IMG_BICUBIC);

                // Having scaled the image (using the bicubic algorithm), remove the 'bleed' and 
                // compose the final avatar.
                $finalAvatar = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresized($finalAvatar, $avatar, 0, 0, 0, 0, $newWidth, $newHeight, $newWidth, $newWidth);

                // Turn the avatar into a string. There is no known save into memory option in GD
                // at the time when this was developed, thus use the output buffer to achive the
                // same effect.
                ob_start();
                imagepng($finalAvatar);
                $avatarAsString = ob_get_clean();

                Storage::disk('local')->put('public/avatars/'.$author->id.'.png', $avatarAsString);
                
                $author->has_avatar = true;
            } catch (Exception $ex) {
                // Images can't be processed, so bail
                $author->has_avatar = false;
            } finally {
                // Ensure to always free up memory.
                if (isset($original) && is_resource($original)) {
                    imagedestroy($original);
                }
                
                if (isset($avatar) && is_resource($avatar)) {
                    imagedestroy($avatar);
                }

                if (isset($finalAvatar) && is_resource($finalAvatar)) {
                    imagedestroy($finalAvatar);
                }
            }

            $author->save();
        }

        unlink($file->path());

        // Register an audit trail for the changed avatar
        $this->_auditTrail->store(AuditTrail::ACTION_PROFILE_EDIT_AVATAR, $author);
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

        return Account::findOrFail($id);
    }
}
