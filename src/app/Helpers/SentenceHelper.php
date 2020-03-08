<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class SentenceHelper
{
    public function buildSentences(Collection $adaptedFragments, string $builderName = null) {
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

    public function combine(Collection $fragments, array $sentenceMappings)
    {
        $parts = [];
        $first = true;

        foreach ($sentenceMappings as $lineMapping) {
            if (! $first) {
                $parts[] = "\n";
            } else {
                $first = false;
            }

            foreach ($lineMapping as $mapping) {
                if (is_array($mapping)) {
                    $parts[] = count($mapping) < 2 
                        ? $fragments[$mapping[0]]['fragment']
                        : $mapping[1];
                } else {
                    $parts[] = $mapping;
                }
            }
        }

        $str = implode('', $parts);
        return $str;
    }
}
