<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KeywordIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropIndex('NormalizedKeywordUnaccentedIndex');
            $table->dropIndex('KeywordsNormalizedKeyword');
            $table->dropIndex('KeywordsReversedNormalizedKeyword');
            $table->dropIndex('ReversedNormalizedKeywordUnaccentedIndex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There's no rolling back as these keywords are deprecated.
    }
}
