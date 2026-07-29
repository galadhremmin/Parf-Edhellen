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
        Schema::create('search_view_hourly_stats', function (Blueprint $table) {
            $table->id();
            $table->dateTime('hour')->unique();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });

        DB::statement(
            "INSERT INTO search_view_hourly_stats (hour, views, created_at, updated_at)
             SELECT
                 DATE_FORMAT(viewed_at, '%Y-%m-%d %H:00:00') AS hour,
                 COUNT(*) AS views,
                 NOW(),
                 NOW()
             FROM search_view_events
             GROUP BY DATE_FORMAT(viewed_at, '%Y-%m-%d %H:00:00')"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_view_hourly_stats');
    }
};
