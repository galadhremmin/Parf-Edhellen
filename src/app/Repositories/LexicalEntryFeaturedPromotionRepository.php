<?php

namespace App\Repositories;

use App\Models\LexicalEntryFeaturedPromotion;

class LexicalEntryFeaturedPromotionRepository
{
    /**
     * Records that an account promoted a lexical entry to the featured (best-match) slot
     * for a given search word and language, and which entry it replaced (the algorithmic
     * pick), if any. This is a write-only analytics signal — it does not affect what any
     * user sees; it is meant to inform future improvements to the ranking algorithm in
     * `BookAdapter`.
     */
    public function record(
        int $accountId,
        string $searchWord,
        int $languageId,
        int $lexicalEntryId,
        ?int $previousLexicalEntryId,
    ): LexicalEntryFeaturedPromotion {
        return LexicalEntryFeaturedPromotion::create([
            'account_id' => $accountId,
            'search_word' => $searchWord,
            'language_id' => $languageId,
            'lexical_entry_id' => $lexicalEntryId,
            'previous_lexical_entry_id' => $previousLexicalEntryId,
        ]);
    }
}
