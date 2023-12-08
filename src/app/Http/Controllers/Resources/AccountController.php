<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;

use App\Http\Controllers\Abstracts\Controller;
use App\Adapters\AuditTrailAdapter;
use App\Models\{
    Account,
    AuditTrail,
    Role
};

class AccountController extends Controller
{
    private $_auditTrailAdapter;

    const PAGINATION_SIZE = 30;

    public function __construct(AuditTrailAdapter $auditTrailAdapter)
    {
        $this->_auditTrailAdapter = $auditTrailAdapter;
    }

    public function index(Request $request)
    {
        $v = $request->validate([
            'filter' => 'string|min:1|max:128'
        ]);

        $query = Account::with('authorization_provider')
            ->orderBy('nickname', 'asc');
        if (isset($v['filter'])) {
            $query = $query->where('nickname', 'like', $v['filter'].'%')
                ->orWhere('email', 'like', $v['filter'].'%');
        }

        $accounts = $query->paginate(self::PAGINATION_SIZE);
        
        return view('admin.account.index', [
            'accounts' => $accounts
        ] + $v);
    }

    public function edit(Request $request, int $id) 
    {
        $account = Account::findOrFail($id);
        $roles   = Role::orderBy('name', 'asc')->get();

        $auditTrailPagination = AuditTrail::forAccount($account->id)->orderBy('id', 'desc')
            ->paginate(15);
        $auditTrail = $this->_auditTrailAdapter->adapt($auditTrailPagination->items());

        return view('admin.account.edit', [
            'account'              => $account,
            'auditTrail'           => $auditTrail,
            'auditTrailPagination' => $auditTrailPagination,
            'roles'                => $roles
        ]);
    }

    public function byRole(Request $request, int $id)
    {
        $role     = Role::findOrFail($id);
        $accounts = $role->accounts()->orderBy('nickname', 'asc')
            ->paginate(self::PAGINATION_SIZE);

        return view('admin.account.by-role-list', [
            'accounts' => $accounts,
            'role'     => $role
        ]);
    }

    public function addMembership(Request $request, int $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $roleId = intval( $request->input('role_id') );
        $role = Role::findOrFail($roleId);

        $account = Account::findOrFail($id);
        $account->addMembershipTo($role->name);

        return redirect()->route('admin.account.edit', ['account' => $account->id]);
    }

    public function deleteMembership(Request $request, int $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $roleId = intval( $request->input('role_id') );
        $role = Role::findOrFail($roleId);

        $account = Account::findOrFail($id);
        $account->removeMembership($role->name);

        return redirect()->route('admin.account.edit', ['account' => $account->id]);
    }
}
