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
        Schema::create('queue_job_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('job_class', 255)->index('idx_qjs_job_class');
            $table->string('queue_name', 64)->index('idx_qjs_queue_name');
            $table->string('status', 16)->index('idx_qjs_status'); // 'success', 'failed', 'retry'
            $table->unsignedInteger('execution_time_ms')->nullable(); // in milliseconds
            $table->unsignedTinyInteger('attempts')->default(1);
            $table->text('error_message')->nullable();
            $table->string('connection', 64)->default('database');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at');
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['job_class', 'status'], 'idx_qjs_class_status');
            $table->index(['queue_name', 'status'], 'idx_qjs_queue_status');
            $table->index(['completed_at', 'status'], 'idx_qjs_date_status');
            $table->index(['job_class', 'completed_at'], 'idx_qjs_class_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_job_statistics');
    }
};
