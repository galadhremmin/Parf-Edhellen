<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Process;

/**
 * Transcribes text to tengwar the way the phrase form does, by handing it to Glaemscribe, which
 * only exists in JavaScript -- see resources/node/transcribe-tengwar.cjs.
 */
class TengwarTranscriber
{
    /**
     * @param  array<int, array{mode: ?string, text: string}>  $items
     * @return array<int, ?string> the tengwar for each item, in order; null where there is none
     *
     * @throws \RuntimeException when Node or Glaemscribe cannot be run
     */
    public function transcribe(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $result = Process::path(base_path())
            ->input(json_encode(['items' => array_values($items)], JSON_UNESCAPED_UNICODE))
            ->timeout(120)
            ->run(['node', resource_path('node/transcribe-tengwar.cjs')]);

        if ($result->failed()) {
            throw new \RuntimeException('Tengwar transcription failed: '.trim($result->errorOutput()));
        }

        $tengwar = json_decode($result->output(), true)['tengwar'] ?? null;
        if (! is_array($tengwar) || count($tengwar) !== count($items)) {
            throw new \RuntimeException('Tengwar transcription returned an unexpected result.');
        }

        return $tengwar;
    }
}
