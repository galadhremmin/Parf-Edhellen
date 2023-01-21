<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WordLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('words', function (Blueprint $table) {
            $table->string('word', 250)->change();
            $table->string('normalized_word', 250)->change();
            $table->string('reversed_normalized_word', 250)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('words', function (Blueprint $table) {
            $table->string('word', 170)->change();
            $table->string('normalized_word', 170)->change();
            $table->string('reversed_normalized_word', 170)->change();
        });
    }
}
