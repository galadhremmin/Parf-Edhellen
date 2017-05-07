<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Models\Account;
use App\Http\Controllers\Controller;

class AccountApiController extends Controller 
{
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
            $query = Account::where('id', intval($nickname));
        }

        $queryByNickname = Account::where('nickname', 'like', $nickname.'%')
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
}