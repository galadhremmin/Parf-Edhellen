<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Language;

class PrimitiveElvish extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Language::where('name', 'Primitive Elvish') //
            ->update(['is_unusual' => '0']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Language::where('name', 'Primitive Elvish') //
            ->update(['is_unusual' => '1']);
    }
}
