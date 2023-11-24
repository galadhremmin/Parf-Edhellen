<?php

namespace App\Http\Controllers\Api\v2;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Storage
};
use Illuminate\Support\Str;

use App\Events\{
    AccountChanged,
    AccountAvatarChanged
};
use App\Helpers\LinkHelper;
use App\Models\Account;
use App\Http\Controllers\Abstracts\Controller;
use App\Helpers\StorageHelper;
use App\Repositories\DiscussRepository;
use Image;

class AccountApiController extends Controller 
{
    private $_storageHelper;
    private $_linkHelper;

    public function __construct(DiscussRepository $discussRepository, StorageHelper $storageHelper, LinkHelper $linkHelper) 
    {
        $this->_discussRepository = $discussRepository;
        $this->_storageHelper = $storageHelper;
        $this->_linkHelper = $linkHelper;
    }

    public function index(Request $request)
    {
        return Account::orderBy('nickname')
            ->select('id', 'nickname')
            ->get();
    }

    public function getAccount(Request $request, int $id)
    {
        return Account::findOrFail($id);
    }

    public function findAccount(Request $request) 
    {
        $this->validate($request, [
            'nickname' => 'required|string',
            'max'      => 'sometimes|numeric|min:1'
        ]);

        $nickname = $request->input('nickname');

        $query = null;
        if (is_numeric($nickname)) {
            $query = Account::where('id', intval($nickname))
                ->select('id', 'nickname');
        }

        $queryByNickname = Account::where('nickname', 'like', $nickname.'%')
            ->select('id', 'nickname')
            ->orderBy('nickname');

        if (! $query) {
            $query = $queryByNickname;
        } else {
            $query = $query->union($queryByNickname);
        }

        if ($request->has('max')) {
            $query = $query->take(intval($request->input('max')));
        }

        return $query->get();
    }

    public function getAvatar(Request $request, int $id)
    {
        $account = Account::find($id);
        return [
            'avatar' => $this->_storageHelper->accountAvatar($account, true)
        ];
    }

    public function getFeatureBackgrounds(Request $request)
    {
        $files = $this->_storageHelper->featureBackgrounds();
        $baseDirectory = null;
        if (! empty($files)) {
            $baseDirectory = pathinfo(Storage::url($files[0]))['dirname'];

            for ($i = 0; $i < count($files); $i += 1) {
                $files[$i] = pathinfo($files[$i])['basename'];
            }
        }

        return [
            'path' => $baseDirectory,
            'files' => $files
        ];
    }

    public function update(Request $request, $id = null)
    {
        $account = $this->getAuthorizedAccount($request, $id);

        $this->validate($request, [
            'nickname' => 'bail|required|unique:accounts,nickname,' . $account->id . ',id|min:3|max:'.config('ed.max_nickname_length')
        ]);

        $account->nickname = $request->input('nickname');
        $account->tengwar  = $request->input('tengwar');
        $account->profile  = $request->input('introduction');

        $changed = $account->isDirty();
        if ($changed) {
            $account->save();

            // Register an audit trail for the changed profile
            event(new AccountChanged($account));
        }

        return [
            'nickname' => $account->nickname,
            'profile_url' => $this->_linkHelper->author($account->id, $account->nickname)
        ];
    }

    public function updateAvatar(Request $request, int $accountId)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096'
        ]);

        $account = $this->getAuthorizedAccount($request, $accountId);
        $file = $request->file('avatar');
        
        if (! $file->isValid()) {
            abort(400, 'Bad avatar image.');
        }

        $localPath = $this->getAvatarPath($accountId);
        $maxSizeInPixels = config('ed.avatar_size');

        try {
            $image = Image::make($file->getRealPath());
            $image->resize($maxSizeInPixels, $maxSizeInPixels, function ($constraint) {
                $constraint->aspectRatio();
            })->save($localPath);
            
            $account->has_avatar = true;
            $account->save();

            // Register an audit trail for the changed avatar
            event(new AccountAvatarChanged($account));
        } finally {
            unlink($file->path());
        }

        return [
            'account_id' => $account->id,
            'avatar_path' => $this->_storageHelper->accountAvatar($account, false /* = _null_ if none exists */)
        ];
    }

    public function updateFeatureBackground(Request $request, int $accountId)
    {
        $request->validate([
            'feature_background_url' => 'string|nullable|max:128'
        ]);

        $file = $request->input('feature_background_url');
        if ($file !== null && ! $this->_storageHelper->isFeatureBackground($file)) {
            abort(400, 'Bad feature background file. You can only choose among the files in the library.');
        }

        $account = $this->getAuthorizedAccount($request, $accountId);
        $account->feature_background_url = $file;
        $account->save();

        return [
            'account_id' => $account->id,
            'feature_background_url' => $file
        ];
    }

    public function delete(Request $request, int $accountId)
    {
        $uuid    = 'DELETED'.Str::uuid();
        $date    = Carbon::now()->toDateTimeString();

        $account = $this->getAuthorizedAccount($request, $accountId);
        $account->is_deleted                 = true;
        $account->nickname                   = sprintf('(Deleted %s)', $date);
        $account->email                      = 'deleted@'.$uuid;
        $account->authorization_provider_id  = null;
        $account->identity                   = $uuid;
        $account->profile                    = 'The user deleted their account on '.$date;
        $account->tengwar                    = null;
        $account->has_avatar                 = 0;
        $account->save();

        $localPath = $this->getAvatarPath($accountId);
        if (file_exists($localPath)) {
            unlink($localPath);
        }

        $redirectUrl = route('logout');
        if (! $request->ajax()) {
            return redirect($redirectUrl);
        }
        return [
            'redirect_to' => $redirectUrl
        ];
    }

    private function getAvatarPath(int $accountId)
    {
        return Storage::path(sprintf('public/avatars/%d.png', $accountId));
    }

    private function getAuthorizedAccount(Request $request, int $accountId): Account
    {
        if ($accountId === null) {
            return $request->user();
        }

        $user = $request->user();
        if ($user->id !== $accountId && ! $user->isAdministrator()) {
            abort(403, sprintf('You are not authorized to perform this operation on account %d.', $accountId));
        }

        $account = Account::findOrFail($accountId);
        return $account;
    }
}
