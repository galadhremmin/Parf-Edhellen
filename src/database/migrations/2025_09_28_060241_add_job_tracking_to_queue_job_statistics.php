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
        Schema::table('queue_job_statistics', function (Blueprint $table) {
            $table->string('job_id', 255)->nullable()->after('connection');
            $table->boolean('is_active')->default(false)->after('started_at');
            
            // Add indexes for efficient querying
            $table->index(['job_id'], 'idx_qjs_job_id');
            $table->index(['is_active', 'started_at'], 'idx_qjs_active_started');
            $table->index(['job_class', 'is_active'], 'idx_qjs_class_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_job_statistics', function (Blueprint $table) {
            $table->dropIndex('idx_qjs_job_id');
            $table->dropIndex('idx_qjs_active_started');
            $table->dropIndex('idx_qjs_class_active');
            $table->dropColumn(['job_id', 'is_active']);
        });
    }
};
