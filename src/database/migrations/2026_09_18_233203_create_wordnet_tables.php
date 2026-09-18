<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Princeton WordNet 3.1, loaded by `wordnet:import`. Read-only reference data: the sense normaliser
        // lemmatises against it and the concept hierarchy is seeded from it.
        Schema::create('wordnet_synsets', function (Blueprint $table) {
            // offset and part of speech, e.g. 12281241-n; offsets are only stable within one WordNet release
            $table->string('id', 12)->primary();
            $table->char('pos', 1);
            $table->string('lexname', 32);
            $table->text('definition');
        });

        Schema::create('wordnet_senses', function (Blueprint $table) {
            $table->string('lemma', 100);
            $table->string('synset_id', 12);
            $table->char('pos', 1);
            $table->unsignedSmallInteger('sense_number');
            $table->unsignedInteger('tag_count');

            $table->primary(['lemma', 'synset_id']);
            $table->index('synset_id');
        });

        Schema::create('wordnet_hypernyms', function (Blueprint $table) {
            $table->string('synset_id', 12);
            $table->string('hypernym_id', 12);
            $table->boolean('is_instance');

            $table->primary(['synset_id', 'hypernym_id']);
            $table->index('hypernym_id');
        });

        Schema::create('wordnet_exceptions', function (Blueprint $table) {
            $table->char('pos', 1);
            $table->string('form', 100);
            $table->string('base', 100);

            $table->primary(['pos', 'form', 'base']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wordnet_exceptions');
        Schema::dropIfExists('wordnet_hypernyms');
        Schema::dropIfExists('wordnet_senses');
        Schema::dropIfExists('wordnet_synsets');
    }
};
