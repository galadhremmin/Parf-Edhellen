<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\GlossGroup;

class FanInventionToReconstruction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        GlossGroup::where('name', 'Eldamo - fan inventions')
            ->update([
                'name'  => 'Eldamo - neologism/reconstructions',
                'label' => 'neologism/reconstruction'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        GlossGroup::where('name', 'Eldamo - neologism/reconstructions')
            ->update([
                'name'  => 'Eldamo - fan inventions',
                'label' => 'fan invention'
            ]);
    }
}
