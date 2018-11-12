<?php
namespace App\Adapters;

use App\Helpers\MarkdownParser;
use App\Adapters\{ LatinSentenceBuilder, TengwarSentenceBuilder };
use App\Models\{
    ModelBase, 
    Inflection
};
use Illuminate\Support\Collection;

class SentenceAdapter
{
    public function buildSentences(array $adaptedFragments, string $builderName = null) {
        $result = [];

        $sentenceBuilders = config('ed.required_sentence_builders');
        foreach ($sentenceBuilders as $name => $class) {
            if ($builderName !== null && $name !== $builderName) {
                continue;
            }

            $builder = new $class($adaptedFragments);
            $result[$name] = $builder->build();
        }

        return $result;
    }
}
