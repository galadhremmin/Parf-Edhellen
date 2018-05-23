<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SystemErrorCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_errors', function (Blueprint $table) {
            $table->string('category', 16)->nullable();
            $table->index('category');
        });

        DB::table('system_errors')
            ->where('message', 'like', 'undefined%')
            ->orWhere('message', 'like', '%Netscape%')
            ->orWhere('message', 'like', '%MSIE%')
            ->orWhere('message', 'like', '%AppleWebKit%')
            ->orWhere('message', 'like', '%Opera%')
            ->update([
                'category' => 'frontend'
            ]);
        
        DB::table('system_errors')
            ->whereNull('category')
            ->update([
                'category' => 'backend'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_errors', function (Blueprint $table) {
            $table->dropIndex('system_errors_category_index');
            $table->dropColumn('category');
        });
    }
}
