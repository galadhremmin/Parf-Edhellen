<?php

namespace App\ThirdParty\Gemini;

use App\Interfaces\IComposesWordOfTheDayTweet;
use App\Models\LexicalEntry;
use Illuminate\Support\Facades\Log;

class GeminiWordOfTheDayFacade extends AbstractGeminiFacade implements IComposesWordOfTheDayTweet
{
    /**
     * Compose a short tweet body for the given lexical entry using Gemini.
     *
     * Falls back to the entry's raw gloss translations if the API key is missing
     * or the request fails.
     */
    public function composeTweet(LexicalEntry $entry): string
    {
        $apiKey = config('gemini.api_key', '');
        if (empty($apiKey)) {
            return $this->_buildFallback($entry);
        }

        try {
            $prompt = $this->_buildPrompt($entry);

            return $this->_callGemini($apiKey, $prompt, false);
        } catch (\Throwable $e) {
            Log::warning('GeminiWordOfTheDayFacade: failed to compose tweet, using fallback.', [
                'error' => $e->getMessage(),
                'lexical_entry_id' => $entry->id,
            ]);

            return $this->_buildFallback($entry);
        }
    }

    private function _buildPrompt(LexicalEntry $entry): string
    {
        $word = $entry->word->word ?? '';
        $languageName = $entry->language->name ?? 'Unknown';
        $shortName = $entry->language->short_name ?? '';
        $speechName = $entry->speech->name ?? '';
        $translations = $entry->glosses
            ->pluck('translation')
            ->filter()
            ->join(', ');

        $etymologyLine = '';
        $etymology = strip_tags(trim((string) $entry->etymology));
        if ($etymology !== '') {
            $etymologyLine = "Etymology: {$etymology}\n";
        }

        $sourceLine = '';
        $source = strip_tags(trim((string) $entry->source));
        if ($source !== '') {
            $sourceLine = "Source: {$source}\n";
        }

        $commentsLine = '';
        $comments = strip_tags(trim((string) $entry->comments));
        if ($comments !== '') {
            $commentsLine = "Notes: {$comments}\n";
        }

        $speechLine = $speechName !== '' ? "Part of speech: {$speechName}\n" : '';

        return <<<PROMPT
You are writing a social-media post (tweet) for elfdict.com, a free dictionary of Tolkien's constructed languages (Quenya, Sindarin, Khuzdul, etc.).

Your task: write a short, engaging description of the word below for a Tolkien language-enthusiast audience.

WORD DATA
Language: {$languageName} (abbreviated: {$shortName})
Word: {$word}
{$speechLine}Translations: {$translations}
{$etymologyLine}{$sourceLine}{$commentsLine}
FORMAT EXAMPLES — for tone reference only, do not reproduce these verbatim:
  "starlight," An (archaic?) name for Starlight, not directly attested in Tolkien's later writing [√(Ñ)GIL]
  "Riddermark, (lit.) Horse-country" — archaic form of the name Rohan.

OUTPUT RULES
- Output ONLY the description text. No language prefix, no URL, no hashtags.
- Start with the primary translation wrapped in double quotes, followed by a comma, then concise commentary.
- Maximum 200 characters total.
- Write in third person. Do not address the reader.
- Mention etymological roots, cognates, or Tolkien's writing context when the data provides them.
- If you are uncertain about any detail, omit it rather than guessing.
- Output plain text only — no markdown, no JSON.
PROMPT;
    }

    private function _buildFallback(LexicalEntry $entry): string
    {
        $translations = $entry->glosses
            ->pluck('translation')
            ->filter()
            ->join('; ');

        return $translations !== '' ? $translations : '(no translation available)';
    }
}
