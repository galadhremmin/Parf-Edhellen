<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per ancestor per derivation hypothesis. A hypothesis (= one deriv in Eldamo,
        // identified by `derivation_group_uuid`) is materialized as its full ancestry chain:
        // `order` 0 is the immediate parent, increasing towards the root. This makes
        // "all words derived from root X" a single indexed query on parent_lexical_entry_id.
        Schema::create('lexical_entry_derivations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lexical_entry_id');
            $table->char('derivation_group_uuid', 36);
            $table->unsignedSmallInteger('order')->default(0);
            $table->unsignedBigInteger('parent_lexical_entry_id')->nullable();
            $table->string('parent_external_id', 128)->nullable();
            $table->string('parent_form', 196);
            $table->unsignedBigInteger('parent_language_id')->nullable();
            $table->boolean('is_uncertain')->default(0);
            $table->boolean('is_rejected')->default(0);
            $table->string('source', 128)->nullable();
            $table->string('comment', 255)->nullable();
            // Intermediate forms between the entry and its immediate parent (Eldamo @i1..@i3),
            // e.g. S. crist < *kiríste* < √KIRIS. Only present on the `order` 0 row.
            $table->json('intermediate_stages')->nullable();
            $table->timestamps();

            $table->index('lexical_entry_id', 'lexical_entry_derivations_lexical_entry_id_index');
            $table->index('parent_lexical_entry_id', 'lexical_entry_derivations_parent_lexical_entry_id_index');
            $table->index('parent_external_id', 'lexical_entry_derivations_parent_external_id_index');
            $table->index('derivation_group_uuid', 'lexical_entry_derivations_derivation_group_uuid_index');

            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries')->onDelete('cascade');
            $table->foreign('parent_lexical_entry_id')->references('id')->on('lexical_entries')->onDelete('set null');
            $table->foreign('parent_language_id')->references('id')->on('languages')->onDelete('set null');
        });

        // Step-by-step phonetic evolution for a derivation hypothesis, e.g.
        // ✶kirissē > kirisse > kriste > crist. The first row is the starting form
        // (no rule); subsequent rows record the rule applied and the form it produced.
        Schema::create('lexical_entry_phonetic_developments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lexical_entry_id');
            $table->char('derivation_group_uuid', 36);
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('word', 196);
            $table->string('rule', 64)->nullable();
            $table->string('previous_word', 196)->nullable();
            $table->unsignedBigInteger('language_id')->nullable();
            $table->timestamps();

            $table->index('lexical_entry_id', 'lexical_entry_phonetic_developments_lexical_entry_id_index');
            $table->index('derivation_group_uuid', 'lexical_entry_phonetic_developments_derivation_group_uuid_index');

            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lexical_entry_phonetic_developments');
        Schema::dropIfExists('lexical_entry_derivations');
    }
};
