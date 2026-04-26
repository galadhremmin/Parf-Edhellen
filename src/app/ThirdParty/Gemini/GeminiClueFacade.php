<?php

namespace App\ThirdParty\Gemini;

use App\Interfaces\IRephrasesCrosswordClues;
use Illuminate\Support\Facades\Log;

class GeminiClueFacade extends AbstractGeminiFacade implements IRephrasesCrosswordClues
{
    /**
     * Rephrase crossword clues using Google Gemini.
     *
     * Sends all clues in a single batch request to minimise API usage.
     * Falls back to the original clues unchanged if the API key is missing,
     * the request fails, or the response cannot be parsed.
     *
     * @param  array<int, array<string, mixed>>  $clues
     * @return array<int, array<string, mixed>>
     */
    public function rephraseClues(array $clues): array
    {
        $apiKey = config('gemini.api_key', '');
        if (empty($apiKey) || empty($clues)) {
            return $clues;
        }

        try {
            $rephrased = $this->_rephraseCluesViaGemini($apiKey, $clues);
        } catch (\Throwable $e) {
            Log::warning('GeminiClueFacade: failed to rephrase clues, using originals.', [
                'error' => $e->getMessage(),
            ]);

            return $clues;
        }

        if (count($rephrased) !== count($clues)) {
            Log::warning('GeminiClueFacade: response count mismatch, using original clues.', [
                'expected' => count($clues),
                'received' => count($rephrased),
            ]);

            return $clues;
        }

        foreach (array_keys($clues) as $i) {
            $text = trim((string) ($rephrased[$i] ?? ''));
            if ($text !== '') {
                $clues[$i]['clue'] = $text;
            }
        }

        return $clues;
    }

    /**
     * Build the prompt, call the Gemini API, and return the decoded JSON array
     * of rephrased clue strings.
     *
     * @param  array<int, array<string, mixed>>  $clues
     * @return array<int, string>
     */
    private function _rephraseCluesViaGemini(string $apiKey, array $clues): array
    {
        $prompt = $this->_buildPrompt($clues);
        $text = $this->_callGemini($apiKey, $prompt, true);

        $decoded = json_decode($text, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException(sprintf(
                'Gemini did not return a JSON array: %s',
                $text,
            ));
        }

        return array_values($decoded);
    }

    /**
     * Build the batch rephrasing prompt sent to Gemini.
     *
     * @param  array<int, array<string, mixed>>  $clues
     */
    private function _buildPrompt(array $clues): string
    {
        $lines = [];
        foreach ($clues as $i => $clue) {
            $n = $i + 1;
            $answer = addslashes((string) ($clue['answer'] ?? ''));
            $def = addslashes((string) ($clue['clue'] ?? ''));
            $speech = trim((string) ($clue['speech_name'] ?? ''));
            $speechPart = $speech !== '' ? ", Part of speech: \"{$speech}\"" : '';
            $lines[] = "{$n}. Answer: \"{$answer}\", Definition: \"{$def}\"{$speechPart}";
        }

        $list = implode("\n", $lines);

        return <<<PROMPT
You are writing clues for a Tolkien constructed-language crossword puzzle.
Rephrase each dictionary definition into a brief (8 words or fewer), indirect crossword-style clue.
Rules:
- Never include the answer word itself.
- Never include an obvious direct synonym that would give away the answer immediately.
- Make the clue evocative or slightly oblique, as a crossword clue would be.
- Keep each clue short and natural.
- If you are uncertain about the meaning or cultural context of a clue and cannot rephrase it confidently without risking an inaccurate or misleading result, return the original definition unchanged. It is better to be cautious than to produce a presumptuous or incorrect clue.

Return ONLY a JSON array of rephrased clue strings in the same order as the input. No explanation, no extra keys.

Clues:
{$list}
PROMPT;
    }
}
