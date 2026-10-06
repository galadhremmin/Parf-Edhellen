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
        Schema::table('accounts', function (Blueprint $table) {
            // When a member hid their welcome, so it can be offered back; `shows_welcome` alone can't tell
            // "hid it" from "never had it".
            $table->timestamp('welcome_dismissed_at')->nullable()->after('shows_welcome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('welcome_dismissed_at');
        });
    }
};
