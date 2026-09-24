<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A sense split into its terms by App\Services\Senses\SenseNormalizer. Position 0 is the headword that
        // groups senses together. Derived data: `senses:normalize` rebuilds it at any time.
        Schema::create('sense_terms', function (Blueprint $table) {
            $table->unsignedBigInteger('sense_id');
            $table->unsignedTinyInteger('position');
            $table->string('term', 250);
            // accent sensitive: Elvish words used as senses (éyë) must not merge with English ones (eye)
            $table->string('term_key', 250)->collation('utf8mb4_bin');
            $table->boolean('is_verb');

            $table->primary(['sense_id', 'position']);
            $table->index('term_key');
            $table->foreign('sense_id')->references('id')->on('senses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sense_terms');
    }
};
