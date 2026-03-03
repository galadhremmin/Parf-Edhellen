<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_definitions', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->string('search_term', 128);
            $table->unsignedBigInteger('language_id')->nullable();
            $table->string('speech_ids', 64)->nullable();
            $table->string('lexical_entry_group_ids', 128)->nullable();
            $table->timestamps();
        });

        Schema::create('search_view_events', function (Blueprint $table) {
            $table->id();
            $table->char('search_id', 32);
            $table->timestamp('viewed_at')->useCurrent();
            $table->foreign('search_id')->references('id')->on('search_definitions')->onDelete('cascade');
            $table->index(['viewed_at', 'search_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_view_events');
        Schema::dropIfExists('search_definitions');
    }
};
