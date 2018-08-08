<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropColumn('sentence_fragment_id');
        });
    }
}
