<?php

use App\Models\Inflection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InflectionIsRestricted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inflections', function (Blueprint $table) {
            $table->boolean('is_restricted')->default(0);
        });

        Inflection::where('group_name', 'Eldamo compatibility (do not use)')
            ->update([
                'is_restricted' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inflections', function (Blueprint $table) {
            $table->dropColumn('is_restricted');
        });
    }
}
