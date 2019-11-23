<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\{
    SentenceFragment
};

class SentenceTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sentence_translations', function (Blueprint $table) {
            $table->integer('sentence_id');
            $table->integer('sentence_number');
            $table->text('translation');
            $table->primary(['sentence_id', 'sentence_number']);
        });
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->integer('sentence_number')->nullable();
        });

        $fragments = SentenceFragment::orderBy('sentence_id', 'asc')
            ->orderBy('order', 'asc')
            ->get();
        
        $sentence_id = 0;
        $sentence_increment = 10;
        $arr = [];
        foreach ($fragments as $fragment) {
            if ($sentence_id !== $fragment->sentence_id) {
                $sentence_id = $fragment->sentence_id;
                $sentence_number = 10;
                $newSentence = false;
            } else if ($fragment->type === 10) {
                if ($newSentence) {
                    $fragment->sentence_number = $sentence_number - $sentence_increment;
                }
            }

            if ($fragment->sentence_number === null) {
                $fragment->sentence_number = $sentence_number;
            }

            if (preg_match('/^[\.\?!]{1}$/', $fragment->fragment)) {
                $sentence_number += $sentence_increment;
                $newSentence = true;
            } else {
                $newSentence = false;
            }

            $fragment->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sentence_translations');
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->dropColumn('sentence_number');
        });
    }
}
