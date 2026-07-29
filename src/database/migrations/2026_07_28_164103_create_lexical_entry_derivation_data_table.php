<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Precomputed, request-time-cheap replacement for what used to be three separate live
        // queries per lexical entry (own derivations, own phonetic developments, and the
        // descendant "Derivatives" tree). Rebuilt wholesale by
        // App\Jobs\RebuildLexicalEntryDerivationData whenever the Eldamo import runs — this data
        // only changes on import, so there's no reason to compute it live.
        Schema::create('lexical_entry_derivation_data', function (Blueprint $table) {
            $table->unsignedBigInteger('lexical_entry_id')->primary();
            $table->json('derivations');
            $table->json('derivatives');
            $table->json('phonetic_developments');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lexical_entry_derivation_data');
    }
};
