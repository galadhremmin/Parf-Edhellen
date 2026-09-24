<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // the cleaned, singular term with its spaces, e.g. "oak-tree": what WordNet is looked up by.
        // `ed-senses:normalize` fills it.
        Schema::table('sense_terms', function (Blueprint $table) {
            $table->string('lemma', 250)->nullable()->after('term');
        });
    }

    public function down(): void
    {
        Schema::table('sense_terms', function (Blueprint $table) {
            $table->dropColumn('lemma');
        });
    }
};
