<?php

use App\Models\GlossDetail;
use Illuminate\Database\Migrations\Migration;

class DeleteGlossDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        GlossDetail::where('category', 'Inflections')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Sorry, there's no walking back from that change!
    }
}
