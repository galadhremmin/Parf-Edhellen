<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_crossword_lexical_entry_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lexical_entry_group_id');
            $table->timestamps();

            $table->foreign('lexical_entry_group_id', 'gcleg_lexical_entry_group_id_foreign')
                ->references('id')
                ->on('lexical_entry_groups')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_crossword_lexical_entry_groups');
    }
};
