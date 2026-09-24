<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // the synset's first word, e.g. "oak": what a concept is called. `ed-import:wordnet` fills it.
        Schema::table('wordnet_synsets', function (Blueprint $table) {
            $table->string('label', 100)->nullable()->after('pos');
        });
    }

    public function down(): void
    {
        Schema::table('wordnet_synsets', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
