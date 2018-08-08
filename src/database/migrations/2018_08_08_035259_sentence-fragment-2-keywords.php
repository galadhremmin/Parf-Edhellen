<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use App\Repositories\KeywordRepository;
use Illuminate\Support\Facades\DB;
use App\Models\{
    SentenceFragment
};

class SentenceFragment2Keywords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->unsignedInteger('sentence_fragment_id')->nullable();
            $table->dropIndex('WordTranslationRelation');
            $table->unique(['word_id', 'gloss_id', 'sentence_fragment_id'], 'WordGlossFragmentRelation');
        });

        $repository = resolve(KeywordRepository::class);
        $fragments = SentenceFragment::whereNotNull('gloss_id')->get();
        foreach ($fragments as $fragment) {

            $repository->createKeyword(
                $fragment->gloss->word,
                $fragment->gloss->sense,
                $fragment->gloss,
                $fragment
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropUnique('WordGlossFragmentRelation');
            $table->dropColumn('sentence_fragment_id');
        });
    }
}
