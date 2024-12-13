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
        Schema::table('contributions', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('sentences', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('sentence_fragment_inflection_rels', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('sentence_translations', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('sentences', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('sentence_fragment_inflection_rels', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('sentence_translations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
