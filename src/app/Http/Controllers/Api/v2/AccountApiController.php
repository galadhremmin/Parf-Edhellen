<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{
    Auth,
    Storage
};

use App\Events\{
    AccountChanged,
    AccountAvatarChanged
};
use App\Helpers\LinkHelper;
use App\Models\Account;
use App\Http\Controllers\Controller;
use App\Helpers\StorageHelper;
use Image;

class AccountApiController extends Controller 
{
    private $_storageHelper;
    private $_linkHelper;

    public function __construct(StorageHelper $storageHelper, LinkHelper $linkHelper) 
    {
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

        $localPath = Storage::disk('local')->path(sprintf('public/avatars/%d.png', $account->id));
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

    private function getAuthorizedAccount(Request $request, int $accountId)
    {
        if ($accountId === null) {
            return $request->user();
        }

        if ($request->user()->id !== $accountId && ! $request->user()->isAdministrator()) {
            abort(403, sprintf('You are not authorized to perform this operation on account %d.', $accountId));
        }

        $account = Account::findOrFail($accountId);
        return $account;
    }
}
