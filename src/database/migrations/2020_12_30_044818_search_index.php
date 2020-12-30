<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\{
    Keyword,
    SearchKeyword
};
use App\Models\Initialization\Morphs;
use App\Helpers\StringHelper;

class SearchIndex extends Migration
{
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

            $table->index(['normalized_keyword', 'normalized_keyword_unaccented'])
                ->name('search_keywords_keyword_index');
            $table->index(['normalized_keyword_reversed', 'normalized_keyword_reversed_unaccented'])
                ->name('search_keywords_keyword_reversed_index');
        });

        foreach (Keyword::cursor() as $keyword) {
            $entities = [];

            if ($keyword->gloss_id) {
                $entities['gloss'] = $keyword->gloss_id;
            }
            if ($keyword->sense_id) {
                $entities['sense'] = $keyword->sense_id;
            }
            if ($keyword->sentence_fragment_id) {
                $entities['fragment'] = $keyword->sentence_fragment_id;
            }

            $normalizedKeyword           = StringHelper::normalize($keyword->keyword, true);
            $normalizedKeywordUnaccented = StringHelper::normalize($keyword->keyword, false);

            $normalizedKeywordReversed           = strrev($normalizedKeyword);
            $normalizedKeywordUnaccentedReversed = strrev($normalizedKeywordUnaccented);

            foreach ($entities as $entityName => $entityId) {
                $data = [
                    'keyword'                                => $keyword->keyword,
                    'normalized_keyword'                     => $normalizedKeyword,
                    'normalized_keyword_unaccented'          => $normalizedKeywordUnaccented,
                    'normalized_keyword_reversed'            => $normalizedKeywordReversed,
                    'normalized_keyword_reversed_unaccented' => $normalizedKeywordUnaccentedReversed,
                    'keyword_length'                         => mb_strlen($keyword->keyword),
                    'normalized_keyword_length'              => mb_strlen($normalizedKeyword),
                    'normalized_keyword_unaccented_length'   => mb_strlen($normalizedKeywordUnaccented),
                    'normalized_keyword_reversed_length'     => mb_strlen($normalizedKeywordUnaccented),
                    'normalized_keyword_reversed_unaccented_length' => mb_strlen($normalizedKeywordUnaccentedReversed),
                    'entity_name'  => $entityName,
                    'entity_id'    => $entityId,
                    'is_old'       => $keyword->is_old,
                    'word'         => empty($keyword->word) ? $keyword->keyword : $keyword->word,
                    'word_id'      => $keyword->word_id,
                ];
                SearchKeyword::create($data);
            }
        }
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
