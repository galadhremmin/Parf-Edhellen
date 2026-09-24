<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Senses whose concept nobody could settle: no candidate fitted, the model was unsure, or it couldn't be
        // asked. An editor decides these; assigning a concept removes the row.
        Schema::create('sense_concept_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('sense_id')->primary();
            // App\Repositories\Enumerations\ConceptReviewReason
            $table->string('reason', 24);
            // what the model answered, or the error that stopped it
            $table->string('detail', 250)->nullable();
            $table->unsignedTinyInteger('confidence')->nullable();
            $table->timestamps();

            $table->index('reason');
            $table->foreign('sense_id')->references('id')->on('senses')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sense_concept_reviews');
    }
};
