<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;
use data\entities\SentenceFragment;

class SentenceHelper
{
    public const TENGWAR = 0;
    public const FRAGMENT = 1;

    public function combine(Collection $fragments, int $type)
    {
        $str = [];

        $numberOfFragments = $fragments->count();
        for ($i = 0; $i < $numberOfFragments; $i += 1) {
            $fragment = $fragments[$i];
            $previousFragment = $i > 0 ? $fragments[$i - 1] : null;

            if ($fragment->is_linebreak) {
                $str[] = "\n";
                continue;
            }
            
            if (! $fragment->isPunctuationOrWhitespace() && 
                ! $fragment->isDot() && 
                ($previousFragment === null || 
                ($previousFragment !== null && ! $previousFragment->isDot()))) {
                $str[] = ' ';
            }
            
            if ($type === self::TENGWAR) {
                $str[] = $fragment->tengwar;

            } else if ($type === self::FRAGMENT) {
                $str[] = $fragment->fragment;
            }
        }

        return implode('', $str);
    }

    public function combineTengwar(Collection $fragments) 
    {
        return $this->combine($fragments, self::TENGWAR);
    }

    public function combineFragments(Collection $fragments) 
    {
        return $this->combine($fragments, self::FRAGMENT);
    }
}