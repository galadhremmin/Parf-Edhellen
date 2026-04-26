<?php

namespace App\ThirdParty\Gemini;

use App\Interfaces\IIdentifiesPhrases;
use Illuminate\Support\Facades\Log;

class GeminiPhrasesFacade extends AbstractGeminiFacade implements IIdentifiesPhrases
{
    /**
     * Detect key phrases and Elvish words from the specified text using Gemini.
     *
     * Falls back to an empty array when the API key is missing or the request fails.
     */
    public function detectKeyPhrases(string $text): array
    {
        $apiKey = config('gemini.api_key', '');
        if (empty($apiKey) || empty(trim($text))) {
            return [];
        }

        try {
            return $this->_detectViaGemini($apiKey, $text);
        } catch (\Throwable $e) {
            Log::warning('GeminiPhrasesFacade: failed to detect key phrases, returning empty.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function _detectViaGemini(string $apiKey, string $text): array
    {
        $prompt = $this->_buildPrompt($text);
        $raw = $this->_callGemini($apiKey, $prompt, true);

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException(sprintf(
                'Gemini did not return a JSON array: %s',
                $raw,
            ));
        }

        $phrases = array_filter(array_map('trim', $decoded), fn ($v) => is_string($v) && $v !== '');

        return array_values(array_unique($phrases));
    }

    private function _buildPrompt(string $text): string
    {
        $escaped = addslashes($text);

        return <<<PROMPT
You are a search-indexing assistant for elfdict.com, a dictionary of Tolkien's constructed languages (Quenya, Sindarin, Khuzdul, Adûnaic, Telerin, etc.).

Analyse the following forum post text and return a JSON array of strings. The array should contain:
1. Key phrases that are relevant for search discoverability (topics, concepts, proper nouns, linguistic terms).
2. Any Elvish, Dwarvish, or other Tolkien constructed-language words that appear in the text — these are especially important for indexing.

Rules:
- Return ONLY a flat JSON array of strings, no explanation, no extra keys, no nesting.
- Each entry should be a short phrase or single word (not a full sentence).
- Deduplicate: do not repeat the same phrase in different casing.
- Omit common English stop-words unless they are part of a meaningful proper noun or phrase.
- Elvish/constructed-language words must always be included even if they appear only once.
- Limit the result to at most 40 phrases total.

Text:
"{$escaped}"
PROMPT;
    }
}
