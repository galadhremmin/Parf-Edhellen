<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\ForumGroup;

class TechnicalSectionDiscuss extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ForumGroup::create([
            'name'        => 'Bugs & issues',
            'description' => 'Report bugs and issues you encounter while using our website.',
            'category'    => 'Technical concerns'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        ForumGroup::where([
            'category'    => 'Technical concerns'
        ])->delete();
    }
}
