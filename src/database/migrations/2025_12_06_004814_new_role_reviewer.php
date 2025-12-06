<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::upsert([
            'name' => \App\Security\RoleConstants::Reviewers,
        ], ['name']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::where('name', \App\Security\RoleConstants::Reviewers)->delete();
    }
};
