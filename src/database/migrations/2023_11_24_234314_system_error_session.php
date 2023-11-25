<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SystemErrorSession extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_errors', function (Blueprint $blueprint) {
            $blueprint->string('session_id', 64)->nullable();
            $blueprint->string('category', 64)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_errors', function (Blueprint $blueprint) {
            $blueprint->dropColumn('session_id');
            $blueprint->string('category', 16)->nullable()->change();
        });
    }
}
