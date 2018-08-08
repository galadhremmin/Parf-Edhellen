<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class GlossHasDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('glosses', function (Blueprint $table) {
            $table->smallInteger('has_details')->default(0);
        });

        DB::connection(null)->unprepared(
            'UPDATE glosses SET has_details = 1 WHERE 0 < (
                SELECT COUNT(*) FROM gloss_details WHERE gloss_id = glosses.id
            )'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('glosses', function (Blueprint $table) {
            $table->dropColumn('has_details');
        });
    }
}
