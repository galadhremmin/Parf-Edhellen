<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\{
    Contribution,
    Flashcard,
    GameWordFinderLanguage,
    Gloss,
    GlossInflection,
    Language,
    SearchKeyword,
    Sentence
};
use App\Models\Versioning\GlossVersion;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $ents = [
            Contribution::class,
            Flashcard::class,
            GameWordFinderLanguage::class,
            Gloss::class,
            GlossInflection::class,
            GlossVersion::class,
            SearchKeyword::class,
            Sentence::class
        ];

        foreach ($ents as $ent) {
            $ent::where('language_id', 40)
                ->update(['language_id' => 104]);
        }

        Language::find(40)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
