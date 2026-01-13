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
        Schema::create('webauthn_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->binary('credential_id')->unique();
            $table->longText('public_key'); // JSON-encoded public key from WebAuthn library
            $table->unsignedBigInteger('counter')->default(0); // Signature counter for cloning detection
            $table->string('display_name', 255); // User-friendly name (e.g., "iPhone", "MacBook Pro")
            $table->string('transport', 255)->nullable(); // Comma-separated: 'usb', 'ble', 'nfc', 'internal', 'hybrid'
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // Foreign key and indexes
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade');
            
            $table->index(['account_id'], 'idx_wc_account_id');
            $table->index(['is_active'], 'idx_wc_is_active');
            $table->index(['account_id', 'is_active'], 'idx_wc_account_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webauthn_credentials');
    }
};
