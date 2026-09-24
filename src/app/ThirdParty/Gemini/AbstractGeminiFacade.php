<?php

namespace App\ThirdParty\Gemini;

use Illuminate\Support\Facades\Http;

abstract class AbstractGeminiFacade
{
    private const GeminiApiBase = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Call the Gemini API with a plain-text prompt and return the raw response text.
     *
     * When $expectJson is true, requests application/json as the response MIME type
     * (the caller is then responsible for decoding the returned string as JSON).
     *
     * A $schema constrains the answer to that shape, and implies JSON.
     *
     * @param  array<string, mixed>|null  $schema
     *
     * @throws \RuntimeException on non-2xx response or unexpected response shape.
     */
    protected function _callGemini(string $apiKey, string $prompt, bool $expectJson = false, ?array $schema = null): string
    {
        $model = config('gemini.model', 'gemini-2.5-flash-lite');
        $url = sprintf('%s/%s:generateContent', self::GeminiApiBase, $model);

        $body = ['contents' => [['parts' => [['text' => $prompt]]]]];
        if ($expectJson || $schema !== null) {
            $body['generationConfig'] = ['responseMimeType' => 'application/json'];
        }

        if ($schema !== null) {
            $body['generationConfig']['responseSchema'] = $schema;
        }

        $response = Http::withQueryParameters(['key' => $apiKey])
            ->timeout(config('gemini.timeout', 30))
            ->post($url, $body);

        if (! $response->successful()) {
            throw new \RuntimeException(sprintf(
                'Gemini API returned HTTP %d: %s',
                $response->status(),
                $response->body(),
            ));
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if ($text === null) {
            throw new \RuntimeException(sprintf(
                'Unexpected Gemini response shape: %s',
                $response->body(),
            ));
        }

        return trim((string) $text);
    }
}
