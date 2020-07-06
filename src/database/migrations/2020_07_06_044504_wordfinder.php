<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\GameWordFinderLanguage;

class Wordfinder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_word_finder_languages', function (Blueprint $table) {
            $table->integer('language_id', false, true);
            $table->string('title', 128);
            $table->string('description', 255);
            $table->timestamps();
            $table->primary('language_id');
        });

        GameWordFinderLanguage::create([
            'language_id' => 1,
            'title'       => 'Sindarin',
            'description' => 'Become a sage of the language of the elves of Middle Earth and the men of Gondor.'
        ]);
        GameWordFinderLanguage::create([
            'language_id' => 2,
            'title'       => 'Quenya',
            'description' => 'Master the language of the High Elves of Valinor and the exiled elves of Beleriand.'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_word_finder_languages');
    }
}
