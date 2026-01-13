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
        Schema::create('webauthn_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('challenge', 255)->unique();
            $table->unsignedBigInteger('account_id')->nullable(); // NULL for login challenges, populated for registration
            $table->string('email', 255)->nullable(); // For login challenges (unauthenticated)
            $table->enum('session_type', ['registration', 'authentication']);
            $table->longText('challenge_data'); // JSON: complete challenge object from library
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Foreign key and indexes
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade');
            
            $table->index(['challenge'], 'idx_ws_challenge');
            $table->index(['account_id'], 'idx_ws_account_id');
            $table->index(['expires_at'], 'idx_ws_expires_at');
            $table->index(['session_type'], 'idx_ws_session_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webauthn_sessions');
    }
};
