<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Language;

class LanguageDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('description', 255)->nullable();
        });

        Language::where('name', 'Quenya')->update([
            'description' => 'Quenya, also called High-elven, is the language of the Quendi, the elves of the West. It was brought to Middle Earth during the exile of the Noldor.'
        ]);
        Language::where('name', 'Sindarin')->update([
            'description' => 'Sindarin is the main Eldarin tongue in Middle-earth, the living vernacular of the Grey-elves.'
        ]);
        Language::where('name', 'Telerin')->update([
            'description' => 'Telerin is the language of the Teleri who first dwelt in Tol Eressëa. It is the language of the sea elves.'
        ]);
        Language::where('name', 'Black Speech')->update([
            'description' => 'Black Speech was devised by Sauron in the Dark Years, and he desired to make it the language of all those that served him, though he failed in that purpose.'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
