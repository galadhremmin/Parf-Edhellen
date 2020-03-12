<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\GlossGroup;

class GlossGroupLabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gloss_groups', function (Blueprint $table) {
            $table->string('label', 32)->nullable();
        });

        // Update existing gloss groups (this is fine if they do not exist)
        GlossGroup::where('name', 'Neologism')
            ->update(['label' => 'Fan-invented']);

        GlossGroup::where('name', 'Subject of debate')
            ->update(['label' => 'Fan-invented (controversial)']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gloss_groups', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
}
