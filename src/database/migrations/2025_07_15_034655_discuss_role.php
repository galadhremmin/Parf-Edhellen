<?php

use App\Models\Account;
use App\Models\AccountRoleRel;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::upsert([
            'name' => \App\Security\RoleConstants::Discuss,
        ], ['name']);

        $roleId = Role::where('name', \App\Security\RoleConstants::Discuss)->value('id');

        $ids = Account::whereNotNull('email_verified_at')
            ->where('is_deleted', false)
            ->pluck('id');
        
        AccountRoleRel::upsert(
            $ids->map(function ($id) use ($roleId) {
                return [
                    'account_id' => $id,
                    'role_id' => $roleId,
                ];
            })->toArray(),
            ['account_id', 'role_id']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        AccountRoleRel::whereHas('role', function ($query) {
            $query->where('name', \App\Security\RoleConstants::Discuss);
        })->delete();

        Role::where('name', \App\Security\RoleConstants::Discuss)->delete();
    }
};
