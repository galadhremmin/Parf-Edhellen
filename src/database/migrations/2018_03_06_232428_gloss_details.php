<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GlossDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gloss_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';	
            
            $table->increments('id');
            $table->timestamps();

            $table->unsignedInteger('gloss_id')->references('id')->on('glosses');
            $table->unsignedInteger('account_id')->references('id')->on('accounts');
            $table->unsignedInteger('order');
            $table->string('category', 128)->charset('utf8');
            $table->text('text')->charset('utf8');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gloss_details');
    }
}
