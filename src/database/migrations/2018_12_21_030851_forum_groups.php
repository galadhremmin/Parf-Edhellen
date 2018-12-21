<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Initialization\Morphs;
use App\Models\{
    Account,
    Contribution,
    Gloss,
    ForumGroup,
    ForumThread,
    Sentence,
};

class ForumGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('role')->nullable();
            $table->timestamps();
        });

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->unsignedInteger('forum_group_id');
        });

        $p0 = new ForumGroup([
            'name' => 'Profiles',
            'role' => Morphs::getAlias(Account::class)
        ]);
        $p0->save();

        $p1 = new ForumGroup([
            'name' => 'Contributions',
            'role' => Morphs::getAlias(Contribution::class)
        ]);
        $p1->save();

        $p2 = new ForumGroup([
            'name' => 'Glossary',
            'role' => Morphs::getAlias(Gloss::class)
        ]);
        $p2->save();

        $p3 = new ForumGroup([
            'name' => 'Phrases',
            'role' => Morphs::getAlias(Sentence::class)
        ]);
        $p3->save();

        $p4 = new ForumGroup([
            'name' => 'Discussion',
        ]);
        $p4->save();

        ForumThread::where([
            'entity_type' => 'account'
        ])->update([
            'forum_group_id' => $p0->id
        ]);

        ForumThread::where([
            'entity_type' => 'contribution'
        ])->update([
            'forum_group_id' => $p1->id
        ]);
        
        ForumThread::where([
            'entity_type' => 'gloss'
        ])->update([
            'forum_group_id' => $p2->id
        ]);
        
        ForumThread::where([
            'entity_type' => 'sentence'
        ])->update([
            'forum_group_id' => $p3->id
        ]);
        
        ForumThread::where([
            'entity_type' => 'discussion'
        ])->update([
            'forum_group_id' => $p4->id
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
            $table->dropColumn('forum_group_id');
        });

        Schema::dropIfExists('forum_groups');
    }
}
