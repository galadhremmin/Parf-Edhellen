<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SearchKeywordsIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->index(['entity_name', 'entity_id']);
            $table->index('normalized_keyword_unaccented');
            $table->index('normalized_keyword_reversed_unaccented');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->dropIndex(['entity_name', 'entity_id']);
            $table->dropIndex('normalized_keyword_unaccented');
            $table->dropIndex('normalized_keyword_reversed_unaccented');
        });
    }
}
