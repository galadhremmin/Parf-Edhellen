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

        GlossGroup::where('name', 'Eldamo - fan adaptations')
            ->update([
                'name'  => 'Eldamo - neologism/adaptations',
                'label' => 'neologism/adaptation'
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

        GlossGroup::where('name', 'Eldamo - neologism/adaptations')
            ->update([
                'name'  => 'Eldamo - fan adaptations',
                'label' => 'fan adaptation'
            ]);
    }
}
