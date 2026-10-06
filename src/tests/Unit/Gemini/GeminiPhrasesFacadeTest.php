<?php

namespace Tests\Unit\Gemini;

use App\ThirdParty\Gemini\GeminiPhrasesFacade;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiPhrasesFacadeTest extends TestCase
{
    private const SampleText = 'Earendil was a mariner that tarried in Arvernien; '
        . 'he built a boat of timber felled in Nimbrethil to journey in.';

    /**
     * Simulates a successful Gemini response and verifies that the facade
     * parses, deduplicates, and returns the phrases correctly.
     */
    public function test_parses_gemini_response_into_unique_phrases(): void
    {
        $this->_fakeGeminiResponse(['Earendil', 'mariner', 'Arvernien', 'Nimbrethil', 'Earendil']);

        $phrases = $this->_makeFacade()->detectKeyPhrases(self::SampleText);

        $this->assertEquals(['Earendil', 'mariner', 'Arvernien', 'Nimbrethil'], $phrases);
    }

    /**
     * Verifies that an empty array is returned (not an exception) when the
     * Gemini API responds with a non-2xx status.
     */
    public function test_returns_empty_array_on_api_error(): void
    {
        Http::fake(fn () => Http::response('Service Unavailable', 503));

        $phrases = $this->_makeFacade()->detectKeyPhrases(self::SampleText);

        $this->assertSame([], $phrases);
    }

    /**
     * Verifies that an empty array is returned immediately when the API key
     * is not configured, without making any HTTP call.
     */
    public function test_returns_empty_array_when_api_key_is_missing(): void
    {
        config(['gemini.api_key' => '']);
        Http::fake(fn () => $this->fail('HTTP should not be called without an API key.'));

        $phrases = $this->_makeFacade()->detectKeyPhrases(self::SampleText);

        $this->assertSame([], $phrases);
    }

    // -------------------------------------------------------------------------

    private function _makeFacade(): GeminiPhrasesFacade
    {
        return new GeminiPhrasesFacade();
    }

    /**
     * Fakes the Gemini HTTP endpoint to return a JSON-encoded array of phrases
     * as the `candidates[0].content.parts[0].text` field.
     */
    private function _fakeGeminiResponse(array $phrases): void
    {
        $body = [
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'text' => json_encode($phrases),
                    ]],
                ],
            ]],
        ];

        Http::fake(fn () => Http::response($body, 200));
    }
}
