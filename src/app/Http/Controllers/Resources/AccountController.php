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
use App\Security\AccountManager;
use App\Security\RoleConstants;

class AccountController extends Controller
{
    private $_accountManager;
    private $_auditTrailAdapter;

    const PAGINATION_SIZE = 30;

    public function __construct(AccountManager $accountManager, AuditTrailAdapter $auditTrailAdapter)
    {
        $this->_accountManager = $accountManager;
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

        $recentlyDeleted = Account::where('is_deleted', 1)
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('admin.account.index', [
            'accounts' => $accounts,
            'deletedAccounts' => $recentlyDeleted
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
        $this->blockAdministratorChanges($account, $request->user());

        if ($role->name === RoleConstants::Root) {
            // There can only be one root account
            $root = $this->_accountManager->getRootAccount();
            if ($root !== null && $root->id !== $account->id) {
                abort(400, 'You can only assign one account to be root.');
            }
        }

        $account->addMembershipTo($role->name);

        return redirect()->route('account.edit', ['account' => $account->id]);
    }

    public function deleteMembership(Request $request, int $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $roleId = intval( $request->input('role_id') );
        $role = Role::findOrFail($roleId);

        if ($role->name === RoleConstants::Root) {
            abort(400, 'Root permissions cannot be revoked.');
        }

        $account = Account::findOrFail($id);
        $this->blockAdministratorChanges($account, $request->user());

        $account->removeMembership($role->name);

        return redirect()->route('account.edit', ['account' => $account->id]);
    }

    private function blockAdministratorChanges(Account $account, Account $withAccount) {
        if ($account->isAdministrator() && $withAccount->id !== $account->id && ! $withAccount->isRoot()) {
            abort(400, 'Only the root account can revoke elevated permissions from other administrators.');
        }
    }
}
