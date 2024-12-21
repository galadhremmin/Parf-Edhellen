<?php

namespace App\Helpers;

use App\Models\Account;
use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Gets relative path to the avatar associated with the specified account. The account
     * entity must have loaded the _has_avatar_ property.
     *
     * @param  bool  $anonymousIfNotExists  - should an anonymous avatar be returned when none exists; else null.
     * @return string
     */
    public function accountAvatar(?Account $account = null, $anonymousIfNotExists = false)
    {
        // A file system check is not performed for performance reasons. The database
        // will therefore have to be current.
        if ($account === null || ! $account->has_avatar) {
            return $anonymousIfNotExists
                ? asset('img/anonymous-profile-picture.png')
                : null;
        } else {
            $path = Storage::url('avatars/'.$account->id.'.png');

            return $path;
        }
    }

    public function getAvatarPath(int $accountId)
    {
        return Storage::path(sprintf('public/avatars/%d.png', $accountId));
    }

    public function featureBackgrounds()
    {
        return Storage::files('public/profile-feature-backgrounds', /* recursive: */ false);
    }

    public function isFeatureBackground(string $path)
    {
        $file = pathinfo($path)['basename'];

        return Storage::exists('public/profile-feature-backgrounds/'.$file);
    }
}
