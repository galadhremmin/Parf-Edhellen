<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\ForumGroup;

class Discuss extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forum_groups', function (Blueprint $table) {
            $table->string('category', 128)->nullable();
            $table->boolean('is_readonly')->default(0);
        });

        ForumGroup::where('name', '<>', 'General conversation')
            ->update(['category' => 'Feedback on content']);
        ForumGroup::where('name', 'General conversation')
            ->update(['category' => 'Discussions']);

        foreach ($this->getGroups() as $group) {
            $group->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->getGroups() as $group) {
            ForumGroup::where([
                'name'        => $group->name,
                'description' => $group->description
            ])->delete();
        }

        Schema::table('forum_groups', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('is_readonly');
        });
    }

    private function getGroups() {
        return [
            new ForumGroup([
                'name'        => 'FAQ',
                'description' => 'A curated list of questions often asked by the community.',
                'category'    => 'Discussions',
                'is_readonly' => 1
            ]),
            new ForumGroup([
                'name'        => 'Translations',
                'description' => 'Translation requests for phrases and words. You can also ask the community to review your translations.',
                'category'    => 'Discussions'
            ]),
            new ForumGroup([
                'name'        => 'Questions',
                'description' => 'Questions about Tolkien\'s languages.',
                'category'    => 'Discussions'
            ])
        ];
    }
}
