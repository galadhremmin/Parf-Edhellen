<?php

use App\Models\Speech;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SpeechIsVerb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('speeches', function (Blueprint $table) {
            $table->boolean('is_verb')->default(false);
        });

        Speech::where('name', 'LIKE', 'verb%')
            ->update([
                'is_verb' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('speeches', function (Blueprint $table) {
            $table->dropColumn('is_verb');
        });
    }
}
