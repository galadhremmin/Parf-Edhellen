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
        Schema::table('gloss_inflections', function (Blueprint $table) {
            $table->mediumText('source')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gloss_inflections', function (Blueprint $table) {
            $table->string('source', 196)->nullable()->change();
        });
    }
};
