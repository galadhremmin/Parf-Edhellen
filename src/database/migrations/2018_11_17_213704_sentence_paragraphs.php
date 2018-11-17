<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\{
    SentenceFragment
};

class SentenceParagraphs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->integer('paragraph_number')->nullable();
        });

        Schema::table('sentence_translations', function (Blueprint $table) {
            $table->integer('paragraph_number')->nullable();
        });

        $fragments = SentenceFragment::orderBy('sentence_id', 'asc')
            ->orderBy('order', 'asc')
            ->get();
        
        $sentence_id = 0;
        $paragraph_number = 0;
        $paragraph_increment = 10;
        foreach ($fragments as $fragment) {
            if ($fragment->sentence_id !== $sentence_id) {
                $sentence_id = $fragment->sentence_id;
                $paragraph_number = 10;
            }
            
            $fragment->paragraph_number = $paragraph_number;
            $fragment->save();

            if ($fragment->type === 10) {
                $paragraph_number += $paragraph_increment;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->dropColumn('paragraph_number');
        });

        Schema::table('sentence_translations', function (Blueprint $table) {
            $table->dropColumn('paragraph_number');
        });
    }
}
