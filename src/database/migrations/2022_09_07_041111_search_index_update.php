<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SearchIndexUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            UPDATE search_keywords
                SET language_id = (SELECT language_id FROM glosses WHERE id = search_keywords.entity_id)
            WHERE entity_name = 'gloss'
                AND language_id IS NULL
        ");

        DB::statement(
            "DROP INDEX IF EXISTS idx_glosses ON glosses"
        );

        Schema::table('translations', function (Blueprint $table) {
            $table->index('gloss_id');
        });
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
