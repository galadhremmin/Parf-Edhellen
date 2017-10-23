<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;

class SentenceHelper
{
    public function combine(array $fragments, array $sentenceMappings)
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
