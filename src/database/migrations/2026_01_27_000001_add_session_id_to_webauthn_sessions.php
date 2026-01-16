<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('webauthn_sessions', function (Blueprint $table) {
            // Add session_id column for safer, indexed lookup
            $table->string('session_id', 36)->nullable()->after('challenge');
            $table->index(['session_id', 'session_type'], 'idx_ws_session_id_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webauthn_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_ws_session_id_type');
            $table->dropColumn('session_id');
        });
    }
};
