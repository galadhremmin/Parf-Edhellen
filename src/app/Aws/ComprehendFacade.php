<?php

namespace App\Aws;

use Aws\Credentials\CredentialProvider;
use Aws\Comprehend\ComprehendClient;
use Aws\Comprehend\Exception\ComprehendException;
use Illuminate\Support\Collection;

use App\Interfaces\{
    IIdentifiesPhrases
};

class ComprehendFacade implements IIdentifiesPhrases
{
    private const AWSComprehendChunkSize = 5000;

    /**
     * Analyzes the specified text and returns a collection of key phrases
     * derived from the text body.
     */
    public function detectKeyPhrases(string $text): Collection
    {
        $chunks = $this->_createChunks($text);
        $keywords = $this->_identifyKeyPhrasesFromChunks($chunks);
        return $keywords;
    }

    /**
     * Split the text body into chunks that can be ingested and processed by 
     * Amazon Comprehend, and coalesce the result to a single collection.
     */
    private function _identifyKeyPhrasesFromChunks(Collection $chunks): Collection
    {
        $allKeywords = collect([]);
        $promises = [];

        $client = $this->_createComprehendClient();
        foreach ($chunks as $chunk) {
            $keywords = $this->_identifyKeyPhrasesFromChunk($client, $chunk);
            $allKeywords = $allKeywords->union($keywords);
        }

        return $allKeywords;
    }

    /**
     * Process the specified chunk using the specified client. The optional 'retry' parameter represents the maximum
     * number of retries when AWS responds with an internal server error.
     */
    private function _identifyKeyPhrasesFromChunk(ComprehendClient &$client, string $chunk, $retries = 1): Collection
    {
        $keywords = collect();

        try {
            $result = $client->detectKeyPhrases([
                'LanguageCode' => 'en',
                'Text'         => $chunk
            ]);

            if (! isset($result['KeyPhrases'])) {
                throw new \RuntimeException(
                    sprintf("Unrecognised Amazon comprehend response payload: %s for chunk: %s.",
                        json_encode($result), $chunk)
                );
            }

            $phrases = $result['KeyPhrases'];
            if (is_array($phrases)) { // protect against `null`
                foreach ($phrases as $phrase) {
                    $keywords->push($phrase['Text']);
                }
            }
        } catch (ComprehendException $ex) {
            switch ($ex->getAwsErrorCode()) {
                case 'InternalServerException':
                    if ($retries > 0) {
                        return $this->_identifyKeyPhrasesFromChunk($client, $chunk, $retries - 1);
                    }
                default:
                    throw new \RuntimeException(sprintf("Failed to process the chunk: %s", $chunk), 0, $ex);
            }
        }

        return $keywords->unique();
    }

    /**
     * Chunks the specific content into ingestiable chunks for Amazon Comprehend.
     * 
     * @param string $content
     * @return Collection
     */
    private function _createChunks(string $content): Collection
    {
        $pos = 0;
        $offset = self::AWSComprehendChunkSize;
        $length = strlen($content); // multibytes are irrelevant here. A single corrupt character is OK.
        $chunks = collect([]);

        while ($pos < $length) {
            if ($pos + $offset > $length) {
                $chunk = substr($content, $length - $offset);
            } else {
                $chunk = substr($content, $pos, $offset);
            }
            
            $chunks->push($chunk);
            $pos += $offset;
        }

        return $chunks;
    }

    private function _createComprehendClient(): ComprehendClient
    {
        $provider = CredentialProvider::defaultProvider();
        return new ComprehendClient([
            'credentials' => $provider,
            'region'      => 'us-east-1',
            'version'     => '2017-11-27'
        ]);
    }
}
