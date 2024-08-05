<?php

use App\Models\Account;
use App\Models\AuditTrail;
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
        Schema::create('account_feeds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Account::class);
            $table->timestamp('happened_at')->useCurrent();
            $table->string('content_name', 32)->nullable();
            $table->unsignedBigInteger('content_id')->nullable();
            $table->unsignedInteger('audit_trail_action_id')->nullable();
            $table->foreignIdFor(AuditTrail::class)->nullable();
            $table->timestamps();

            $table->index(['account_id', 'happened_at']);
        });

        Schema::create('account_feed_refresh_times', function (Blueprint $table) {
            $table->foreignIdFor(Account::class);
            $table->string('feed_content_name', 32);
            $table->timestamp('oldest_happened_at')->nullable();
            $table->timestamp('newest_happened_at')->nullable();
            $table->timestamps();

            $table->primary(['account_id', 'feed_content_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_feeds');
        Schema::dropIfExists('account_feed_refresh_times');
    }
};
