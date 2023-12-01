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
        Schema::create('account_merge_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->unsignedBigInteger('account_id');
            $table->json('account_ids');
            $table->string('verification_token', 255);
            $table->unsignedBigInteger('requester_account_id');
            $table->string('requester_ip', 16);
            $table->boolean('is_fulfilled')->default(false);

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('requester_account_id')->references('id')->on('accounts')->cascadeOnDelete();

            $table->unique(['account_id', 'verification_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_merge_requests');
    }
};
