<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Account;

class AccountPrivacyController extends Controller
{
    public function privacy()
    {
        $user = request()->user();
        $accounts = Account::with('authorization_provider') //
            ->where('email', $user->email) //
            ->get();

        return view('account.privacy', [
            'user' => $user,
            'accounts' => $accounts
        ]);
    }
}
