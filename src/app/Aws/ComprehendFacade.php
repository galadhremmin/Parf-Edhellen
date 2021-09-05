<?php

namespace App\Aws;

use Aws\Credentials\CredentialProvider;
use Aws\Comprehend\ComprehendClient;
use Aws\Comprehend\Exception\ComprehendException;

use App\Interfaces\{
    IIdentifiesPhrases
};

class ComprehendFacade implements IIdentifiesPhrases
{
    private const AWSComprehendChunkSize = 5000;

    public function __construct(ComprehendFactory $factory)
    {
        $this->_clientFactory = $factory;
    }

    /**
     * Analyzes the specified text and returns a collection of key phrases
     * derived from the text body.
     */
    public function detectKeyPhrases(string $text): array
    {
        $chunks = $this->_createChunks($text);
        $keywords = $this->_identifyKeyPhrasesFromChunks($chunks);
        return array_unique($keywords);
    }

    /**
     * Split the text body into chunks that can be ingested and processed by 
     * Amazon Comprehend, and coalesce the result to a single collection.
     */
    private function _identifyKeyPhrasesFromChunks(array $chunks): array
    {
        $allPhrases = [];
        $promises = [];

        $client = $this->_clientFactory->create();
        foreach ($chunks as $chunk) {
            $phrases = $this->_identifyKeyPhrasesFromChunk($client, $chunk);
            $allPhrases = array_merge($allPhrases, $phrases);
        }

        return array_values(array_unique($allPhrases));
    }

    /**
     * Process the specified chunk using the specified client. The optional 'retry' parameter represents the maximum
     * number of retries when AWS responds with an internal server error.
     */
    private function _identifyKeyPhrasesFromChunk(ComprehendClient &$client, string $chunk, $retries = 1): array
    {
        $allPhrases = [];

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
                    $allPhrases[] = $phrase['Text'];
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

        return $allPhrases;
    }

    /**
     * Chunks the specific content into ingestiable chunks for Amazon Comprehend.
     * 
     * @param string $content
     * @return array
     */
    private function _createChunks(string $content): array
    {
        $pos = 0;
        $offset = self::AWSComprehendChunkSize;
        $length = strlen($content); // multibytes are irrelevant here. A single corrupt character is OK.
        $chunks = [];

        while ($pos < $length) {
            if ($pos + $offset > $length) {
                $chunk = substr($content, $length - $offset);
            } else {
                $chunk = substr($content, $pos, $offset);
            }
            
            $chunks[] = $chunk;
            $pos += $offset;
        }

        return $chunks;
    }
}
