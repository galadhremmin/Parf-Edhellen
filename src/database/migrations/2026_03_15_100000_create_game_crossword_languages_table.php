<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_crossword_languages', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->primary();
            $table->string('title', 128)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });

        // Seed Quenya (q) and Sindarin (s) as default crossword languages.
        $languageIds = DB::table('languages')
            ->whereIn('short_name', ['q', 's'])
            ->pluck('id');

        $now = now();
        foreach ($languageIds as $id) {
            DB::table('game_crossword_languages')->insertOrIgnore([
                'language_id' => $id,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('game_crossword_languages');
    }
};
