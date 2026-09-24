<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // What senses mean, arranged in a hierarchy: oak → tree → woody plant. Seeded from WordNet; editors can
        // add concepts WordNet lacks, which have no synset.
        Schema::create('concepts', function (Blueprint $table) {
            $table->id();
            $table->string('label', 250);
            $table->string('synset_id', 12)->nullable()->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('concepts')->nullOnDelete();
        });

        // Every ancestor of every concept, itself included at depth 0, so "everything under tree" is one lookup.
        // Derived from concepts.parent_id and rebuilt whenever the hierarchy changes.
        Schema::create('concept_closure', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->unsignedTinyInteger('depth');

            $table->primary(['ancestor_id', 'descendant_id']);
            $table->index('descendant_id');
            $table->foreign('ancestor_id')->references('id')->on('concepts')->cascadeOnDelete();
            $table->foreign('descendant_id')->references('id')->on('concepts')->cascadeOnDelete();
        });

        // The words a concept goes by, keyed like sense_terms, so a search for "timber" finds the concept "wood".
        Schema::create('concept_labels', function (Blueprint $table) {
            $table->string('term_key', 250)->collation('utf8mb4_bin');
            $table->unsignedBigInteger('concept_id');

            $table->primary(['term_key', 'concept_id']);
            $table->index('concept_id');
            $table->foreign('concept_id')->references('id')->on('concepts')->cascadeOnDelete();
        });

        Schema::create('sense_concepts', function (Blueprint $table) {
            $table->unsignedBigInteger('sense_id');
            $table->unsignedBigInteger('concept_id');
            // the sense term the concept was assigned for; 0 is the headword
            $table->unsignedTinyInteger('position');
            // App\Repositories\Enumerations\ConceptSource: who made the assignment
            $table->string('source', 16);
            // 0-100, as judged by whoever assigned it; null for rules and editors
            $table->unsignedTinyInteger('confidence')->nullable();
            // set by editors: no automated run may change a locked assignment
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->primary(['sense_id', 'concept_id']);
            $table->index('concept_id');
            $table->foreign('sense_id')->references('id')->on('senses')->cascadeOnDelete();
            $table->foreign('concept_id')->references('id')->on('concepts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sense_concepts');
        Schema::dropIfExists('concept_labels');
        Schema::dropIfExists('concept_closure');
        Schema::dropIfExists('concepts');
    }
};
