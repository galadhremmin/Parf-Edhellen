<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MasterAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('password', 255)->nullable();
            $table->boolean('is_passworded')->default(false);
            $table->boolean('is_master_account')->default(false);

            $table->unsignedBigInteger('master_account_id')->nullable();
            $table->foreign('master_account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->dropColumn('is_passworded');
            $table->dropColumn('is_master_account');
            $table->dropConstrainedForeignId('master_account_id');
        });
    }
}
