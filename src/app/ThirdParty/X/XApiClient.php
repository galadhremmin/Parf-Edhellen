<?php

namespace App\ThirdParty\X;

use App\Interfaces\IPostsTweet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class XApiClient implements IPostsTweet
{
    private const TweetsEndpoint = 'https://api.twitter.com/2/tweets';

    /**
     * Post a tweet to X using OAuth 1.0a request signing.
     *
     * Returns true on success. Logs a warning and returns false when credentials
     * are missing or the API call fails, so the command can handle it gracefully.
     */
    public function postTweet(string $text): bool
    {
        $apiKey = config('x.api_key', '');
        $apiSecret = config('x.api_secret', '');
        $accessToken = config('x.access_token', '');
        $accessSecret = config('x.access_token_secret', '');

        if ($apiKey === '' || $apiSecret === '' || $accessToken === '' || $accessSecret === '') {
            Log::warning('XApiClient: one or more credentials are missing, skipping post.');

            return false;
        }

        try {
            $authHeader = $this->_buildAuthorizationHeader(
                $apiKey, $apiSecret, $accessToken, $accessSecret,
            );

            $response = Http::withHeaders([
                'Authorization' => $authHeader,
            ])->post(self::TweetsEndpoint, ['text' => $text]);

            if (! $response->successful()) {
                Log::warning('XApiClient: X API returned a non-2xx response.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('XApiClient: exception while posting tweet.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build the OAuth 1.0a Authorization header for a POST request to the tweets endpoint.
     */
    private function _buildAuthorizationHeader(
        string $apiKey,
        string $apiSecret,
        string $accessToken,
        string $accessSecret,
    ): string {
        $oauthParams = [
            'oauth_consumer_key' => $apiKey,
            'oauth_nonce' => Str::random(32),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) time(),
            'oauth_token' => $accessToken,
            'oauth_version' => '1.0',
        ];

        $oauthParams['oauth_signature'] = $this->_sign(
            $oauthParams, $apiSecret, $accessSecret,
        );

        $parts = [];
        foreach ($oauthParams as $key => $value) {
            $parts[] = rawurlencode($key).'="'.rawurlencode($value).'"';
        }

        return 'OAuth '.implode(', ', $parts);
    }

    /**
     * Compute the OAuth 1.0a HMAC-SHA1 signature.
     *
     * Algorithm:
     *   1. Sort all OAuth parameters alphabetically by key.
     *   2. Percent-encode every key and value, join as key=value pairs separated by &.
     *   3. Build the signature base string: METHOD & encoded_url & encoded_params.
     *   4. Build the signing key: encoded_api_secret & encoded_access_secret.
     *   5. Return base64( HMAC-SHA1( signing_key, base_string ) ).
     *
     * @param  array<string, string>  $oauthParams
     */
    private function _sign(array $oauthParams, string $apiSecret, string $accessSecret): string
    {
        ksort($oauthParams);

        $paramParts = [];
        foreach ($oauthParams as $key => $value) {
            $paramParts[] = rawurlencode($key).'='.rawurlencode($value);
        }

        $baseString = implode('&', [
            'POST',
            rawurlencode(self::TweetsEndpoint),
            rawurlencode(implode('&', $paramParts)),
        ]);

        $signingKey = rawurlencode($apiSecret).'&'.rawurlencode($accessSecret);

        return base64_encode(hash_hmac('sha1', $baseString, $signingKey, binary: true));
    }
}
