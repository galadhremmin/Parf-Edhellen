<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\GameWordFinderGlossGroup;

class WordFinderGlossGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_word_finder_gloss_groups', function (Blueprint $table) {
            $table->integer('gloss_group_id', /* autoincrement: */ false);
            $table->timestamps();
            $table->foreign('gloss_group_id')->reference('id')->on('gloss_groups')->name('fk_gloss_groups');
            $table->primary('gloss_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_word_finder_gloss_groups');
    }
}
