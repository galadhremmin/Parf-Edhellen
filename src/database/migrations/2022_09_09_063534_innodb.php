<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{
    DB,
    Schema
};

class Innodb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('version');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('version', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->float('version')->primary();
            $table->date('date');
        });

        DB::statement("INSERT INTO `version` VALUES (1.1,'2015-01-05 13:35:19'),(1.2,'2015-03-07 17:23:56'),(1.3,'2015-07-20 15:14:26'),(1.4,'2015-07-23 15:16:05'),(1.5,'2015-07-24 23:23:59'),(1.6,'2015-11-04 17:18:00'),(1.7,'2017-03-03 11:17:04'),(1.8,'2017-05-04 16:59:59'),(1.9,'2017-05-04 17:00:50'),(1.96,'2017-05-23 23:43:17'),(1.97,'2017-05-26 15:46:19'),(1.98,'2017-06-21 13:15:46'),(1.981,'2017-06-21 14:05:48'),(1.99,'2017-07-13 08:57:41'),(1.991,'2017-07-17 08:07:14'),(1.992,'2017-07-18 13:10:42'),(1.993,'2017-07-31 10:51:46'),(1.994,'2017-09-01 14:33:02'),(1.995,'2017-09-07 15:01:57'),(1.996,'2017-09-12 09:04:16'),(2,'2017-09-22 07:02:35'),(2.1,'2017-10-05 10:48:12'),(2.2,'2017-10-17 16:51:20'),(3.1,'2017-10-25 15:51:09'),(4,'2017-11-01 13:59:35'),(4.1,'2017-12-12 09:47:00'),(4.2,'2018-01-21 06:38:44')");
    }
}
