<?php

use App\Models\GlossGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameHisweloke extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        GlossGroup::where('name', 'Hiswelókë\'s Sindarin Dictionary')->update([
            'name' => 'SINDICT'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        GlossGroup::where('name', 'SINDICT')->update([
            'name' => 'Hiswelókë\'s Sindarin Dictionary'
        ]);
    }
}
