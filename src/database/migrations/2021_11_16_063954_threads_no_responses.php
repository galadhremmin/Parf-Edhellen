<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ForumThread;

class ThreadsNoResponses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->boolean('is_empty')->default(true);
        });

        ForumThread::where('number_of_posts', '>', '1')
            ->update([
                'is_empty' => false,
                'updated_at' => DB::raw('updated_at') // Do not change this value.
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropColumn('is_empty');
        });
    }
}
