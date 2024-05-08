<?php

use App\Models\AccountRoleRel;
use App\Models\Role;
use App\Security\RoleConstants;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::create([
            'name' => RoleConstants::Root
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = Role::where('name', RoleConstants::Root)->first();
        if ($role !== null) {
            AccountRoleRel::where('role_id', $role->id)->delete();
        }

        $role->delete();
    }
};
