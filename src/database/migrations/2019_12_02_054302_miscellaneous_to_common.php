<?php

use App\Models\ForumGroup;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MiscellaneousToCommon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ForumGroup::where('name', 'Miscellaneous & more')
            ->update(['name' => 'General conversation']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        ForumGroup::where('name', 'General conversation')
            ->update(['name' => 'Miscellaneous & more']);
    }
}
