<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Events\AccountSecurityActivityResultEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_security_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('type', 16); // 'login' or 'registration'
            $table->text('assessment'); // JSON output from reCAPTCHA API
            $table->enum('result', array_column(AccountSecurityActivityResultEnum::cases(), 'value')); // Login attempt result
            $table->string('ip_address', 45)->nullable(); // Support for IPv4 and IPv6
            $table->string('user_agent', 512)->nullable(); // User agent string
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['account_id'], 'idx_ase_account_id');
            $table->index(['type'], 'idx_ase_type');
            $table->index(['result'], 'idx_ase_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_security_events');
    }
};
