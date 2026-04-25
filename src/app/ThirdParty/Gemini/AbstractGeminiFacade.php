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
     * @throws \RuntimeException on non-2xx response or unexpected response shape.
     */
    protected function _callGemini(string $apiKey, string $prompt, bool $expectJson = false): string
    {
        $model = config('gemini.model', 'gemini-2.5-flash-lite');
        $url = sprintf('%s/%s:generateContent', self::GeminiApiBase, $model);

        $body = ['contents' => [['parts' => [['text' => $prompt]]]]];
        if ($expectJson) {
            $body['generationConfig'] = ['responseMimeType' => 'application/json'];
        }

        $response = Http::withQueryParameters(['key' => $apiKey])
            ->timeout(30)
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
