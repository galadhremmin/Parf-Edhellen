<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\{
    Keyword,
    SentenceFragment
};
use App\Repositories\KeywordRepository;

class SentenceFragmentKeywordsRefresh extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Keyword::whereNotNull('sentence_fragment_id')
            ->delete();

        $r = resolve(KeywordRepository::class);
        $all = SentenceFragment::where('type', 0)->get();
        foreach ($all as $f) {
            $r->createKeyword($f->gloss->word, $f->gloss->sense, $f->gloss, $f->fragment, $f->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
