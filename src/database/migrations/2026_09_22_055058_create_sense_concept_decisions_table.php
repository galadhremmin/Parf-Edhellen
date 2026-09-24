<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every decision about what a sense means, as it was made: which meanings were on offer, what was answered,
        // how sure it was, and what came of it. Append-only, and deliberately without a foreign key to senses, so
        // that deleting a sense cannot erase the record of what was decided about it.
        Schema::create('sense_concept_decisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sense_id');
            // the sense as it read at the time
            $table->string('sense', 250);
            // App\Repositories\Enumerations\ConceptSource
            $table->string('source', 16);
            // App\Services\Enumerations\ConceptOutcome
            $table->string('outcome', 32);
            // who or what decided: a model and its version, or a rule
            $table->string('decided_by', 64);
            // the WordNet meanings the judge could choose from
            $table->json('candidate_synset_ids')->nullable();
            // what it answered
            $table->json('synset_ids')->nullable();
            $table->string('better_word', 100)->nullable();
            $table->string('relation', 16)->nullable();
            $table->unsignedTinyInteger('confidence')->nullable();
            // the concepts the answer led to, by ID and label
            $table->json('concepts')->nullable();
            // App\Repositories\Enumerations\ConceptReviewReason, when it went to an editor instead
            $table->string('review_reason', 24)->nullable();
            // identifies the prompt the answer was given to, which is otherwise rebuildable from the data
            $table->char('prompt_hash', 40)->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('sense_id');
            $table->index('source');
            $table->index('created_at');
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sense_concept_decisions');
    }
};
