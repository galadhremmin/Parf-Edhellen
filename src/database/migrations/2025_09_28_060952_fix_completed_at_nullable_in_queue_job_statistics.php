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
            $table->timestamp('completed_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_job_statistics', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable(false)->change();
        });
    }
};
