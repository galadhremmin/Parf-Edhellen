<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Models\Account;
use App\Http\Controllers\Controller;
use App\Helpers\StorageHelper;

class AccountApiController extends Controller 
{
    private $_storageHelper;

    public function __construct(StorageHelper $storageHelper) 
    {
        $this->_storageHelper = $storageHelper;
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
}
