<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use App\Models\Account;

class StorageHelper
{
    /**
     * Gets relative path to the avatar associated with the specified account. The account
     * entity must have loaded the _has_avatar_ property.
     *
     * @param Account $account
     * @param boolean $anonymousIfNotExists - should an anonymous avatar be returned when none exists; else null.
     * @return string
     */
    public function accountAvatar(Account $account = null, $anonymousIfNotExists = false)
    {
        // A file system check is not performed for performance reasons. The database
        // will therefore have to be current.
        if ($account === null || ! $account->has_avatar) {
            return $anonymousIfNotExists 
                ? asset('img/anonymous-profile-picture.png') 
                : null;
        } else {
            return Storage::url('avatars/'.$account->id.'.png');
        }
    }
}
