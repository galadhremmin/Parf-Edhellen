<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Language;

class EldamoLanguagesUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Language::create([
            'name' => 'Early Quenya',
            'is_invented' => 1,
            'category' => 'Early Period (1910-1930)',
            'description' => 'Quenya as Tolkien conceived of it in his Early Period (1910-1930 in the terminology of this lexicon). At this stage, it was generally spelled Qenya (and was sometimes called Eldarissa or Eldar).',
            'short_name' => 'eq',
            'is_unusual' => 1,
            'order' => 20
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Language::where('name', 'Early Quenya')->delete();
    }
}
