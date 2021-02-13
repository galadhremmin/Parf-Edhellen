<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\{
    Gloss,
    Keyword,
    SearchKeyword
};
use App\Models\Initialization\Morphs;
use App\Helpers\StringHelper;

class SearchIndex extends Migration
{
    public $withinTransaction = true;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_keywords', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            
            $table->increments('id');
            $table->timestamps();

            $table->integer('search_group', 0, true);
            $table->string('keyword', 250)->charset('utf8');
            $table->string('normalized_keyword', 250)->charset('utf8');
            $table->string('normalized_keyword_reversed', 250)->charset('utf8');
            $table->string('normalized_keyword_unaccented', 250)->charset('utf8');
            $table->string('normalized_keyword_reversed_unaccented', 250)->charset('utf8');

            $table->integer('keyword_length');
            $table->integer('normalized_keyword_length');
            $table->integer('normalized_keyword_reversed_length');
            $table->integer('normalized_keyword_unaccented_length');
            $table->integer('normalized_keyword_reversed_unaccented_length');

            $table->string('entity_name', 32)->charset('utf8');
            $table->integer('entity_id');

            $table->string('word', 250)->charset('utf8');
            $table->integer('word_id')->references('id')->on('words');

            $table->integer('language_id')->nullable();
            $table->integer('speech_id')->nullable();
            $table->integer('gloss_group_id')->nullable();
            $table->boolean('is_old')->nullable();

            $table->index('normalized_keyword_unaccented')
                ->name('search_keywords_kw_ua_index');
            $table->index('normalized_keyword_reversed_unaccented')
                ->name('search_keywords_keyword_kw_r_ua_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_keywords');
    }
}
