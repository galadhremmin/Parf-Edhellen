<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeInflectionNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gloss_inflections', function (Blueprint $table) {
            $table->unsignedBigInteger('inflection_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gloss_inflections', function (Blueprint $table) {
            $table->unsignedBigInteger('inflection_id')->change();
        });
    }
}
