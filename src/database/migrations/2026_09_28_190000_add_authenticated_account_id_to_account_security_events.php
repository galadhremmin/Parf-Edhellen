<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_security_events', function (Blueprint $table) {
            // Captures the account that actually authenticated when a security
            // event is recorded against a different account, e.g. an OAuth
            // sign-in that establishes the session as the IDP account's master.
            $table->foreignId('authenticated_account_id')->nullable()->after('account_id')
                ->constrained('accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_security_events', function (Blueprint $table) {
            $table->dropForeign(['authenticated_account_id']);
            $table->dropColumn('authenticated_account_id');
        });
    }
};
