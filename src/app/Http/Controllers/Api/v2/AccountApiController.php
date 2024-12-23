<?php

namespace App\Http\Controllers\Api\v2;

use App\Events\AccountAvatarChanged;
use App\Events\AccountChanged;
use App\Helpers\LinkHelper;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Account;
use App\Security\AccountManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class AccountApiController extends Controller
{
    private StorageHelper $_storageHelper;

    private LinkHelper $_linkHelper;

    private AccountManager $_accountManager;

    public function __construct(StorageHelper $storageHelper, LinkHelper $linkHelper,
        AccountManager $accountManager)
    {
        $this->_storageHelper = $storageHelper;
        $this->_linkHelper = $linkHelper;
        $this->_accountManager = $accountManager;
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
            'max' => 'sometimes|numeric|min:1',
        ]);

        $nickname = $request->input('nickname');

        $query = null;
        if (is_numeric($nickname)) {
            $query = Account::where('id', intval($nickname))
                ->select('id', 'nickname');
        }

        $queryByNickname = Account::where('nickname', 'like', $nickname.'%')
            ->whereNull('master_account_id')
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
            'avatar' => $this->_storageHelper->accountAvatar($account, true),
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
            'files' => $files,
        ];
    }

    public function update(Request $request, $id = null)
    {
        $account = $this->getAuthorizedAccount($request, $id);

        $this->validate($request, [
            'nickname' => 'bail|required|unique:accounts,nickname,'.$account->id.',id|min:3|max:'.config('ed.max_nickname_length'),
            'tengwar' => 'string|max:64',
        ]);

        $account->nickname = $request->input('nickname');
        $account->tengwar = $request->input('tengwar');
        $account->profile = $request->input('introduction');

        $changed = $account->isDirty();
        if ($changed) {
            $account->save();

            // Register an audit trail for the changed profile
            event(new AccountChanged($account));
        }

        return [
            'nickname' => $account->nickname,
            'profile_url' => $this->_linkHelper->author($account->id, $account->nickname),
        ];
    }

    public function updateAvatar(Request $request, int $accountId)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $account = $this->getAuthorizedAccount($request, $accountId);
        $file = $request->file('avatar');

        if (! $file->isValid()) {
            abort(400, 'Bad avatar image.');
        }

        $localPath = $this->_storageHelper->getAvatarPath($accountId);
        $maxSizeInPixels = config('ed.avatar_size');

        try {
            $image = Image::make($file->getRealPath());

            if ($image->width() < $image->height()) {
                $image->resize($maxSizeInPixels, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($localPath);
            } else {
                $image->resize(null, $maxSizeInPixels, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($localPath);
            }

            $account->has_avatar = true;
            $account->save();

            // Register an audit trail for the changed avatar
            event(new AccountAvatarChanged($account));
        } finally {
            unlink($file->path());
        }

        return [
            'account_id' => $account->id,
            'avatar_path' => $this->_storageHelper->accountAvatar($account, false /* = _null_ if none exists */),
        ];
    }

    public function updateFeatureBackground(Request $request, int $accountId)
    {
        $request->validate([
            'feature_background_url' => 'string|nullable|max:128',
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
            'feature_background_url' => $file,
        ];
    }

    public function updateVerifyEmail(Request $request, int $accountId)
    {
        $request->validate([
            'is_verified' => 'required|boolean',
        ]);

        $verify = boolval($request->input('is_verified'));

        $account = $this->getAuthorizedAccount($request, $accountId);
        $account->email_verified_at = $verify ? Carbon::now() : null;
        $account->save();

        if (! $request->ajax()) {
            return redirect(
                route('account.edit', ['account' => $account])
            );
        }

        return [
            'email_verified_at' => $account->email_verified_at,
        ];
    }

    public function delete(Request $request, int $accountId)
    {
        $account = $this->getAuthorizedAccount($request, $accountId);
        if ($account->isRoot()) {
            abort(400, 'You cannot delete a root account.');
        }

        if ($account->isAdministrator() && ! $request->user()->isRoot()) {
            abort(400, 'You cannot delete an administrator\'s account.');
        }

        $this->_accountManager->delete($account);

        $redirectUrl = route('logout');
        if ($request->user()->id !== $account->id &&
            $request->user()->isAdministrator()) {
            // This is only possible if an administrator deleted the account.
            $redirectUrl = route('account.index');
        }

        if (! $request->ajax()) {
            return redirect($redirectUrl);
        }

        return [
            'redirect_to' => $redirectUrl,
        ];
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
