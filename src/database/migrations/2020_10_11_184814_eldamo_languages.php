<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Language;

class EldamoLanguages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('short_name', 6)->change();
        });

        Language::create([
            'name' => 'North Sindarin',
            'is_invented' => 1,
            'category' => 'Late Period (1950-1973)',
            'description' => 'North Sindarin is a dialect of Sindarin spoken in northern Beleriand where it was adopted by many of the Noldor, most notably the followers of the sons of Fëanor.',
            'short_name' => 'norths',
            'is_unusual' => 0,
            'tengwar_mode' => 'sindarin-tengwar-beleriand',
            'order' => 40
        ]);
        Language::create([
            'name' => 'Middle Ancient Quenya',
            'is_invented' => 1,
            'category' => 'Middle Period (1930-1950)',
            'description' => 'The earlier conceptual stage for Ancient Quenya, from the 1930s and 1940s.',
            'short_name' => 'maq',
            'is_unusual' => 0,
            'order' => 30
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Language::whereIn('name', ['North Sindarin', 'Middle Ancient Quenya'])->delete();

        Schema::table('languages', function (Blueprint $table) {
            $table->string('short_name', 4)->change();
        });
    }
}
