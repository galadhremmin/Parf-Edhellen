<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Models\Interfaces\IHasFriendlyName;
use App\Models\{
    AuditTrail,
    ForumThread
};

class DiscussSlugs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audit_trails', function (Blueprint $table) {
            $table->string('entity_name', 512)->nullable();
        });

        AuditTrail::get()->each(function ($audit) {
            $entity = $audit->entity;
            if ($entity instanceOf IHasFriendlyName) {
                $audit->entity_name = $entity->getFriendlyName();
                $audit->save();
            }
        });

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->string('normalized_subject', 512)->nullable();
        });

        ForumThread::get()->each(function ($thread) {
            $thread->normalized_subject = StringHelper::normalize($thread->subject);
            $thread->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audit_trails', function (Blueprint $table) {
            $table->dropColumn('entity_name');
        });

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropColumn('normalized_subject');
        });
    }
}
