<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Illuminate\Contracts\Support\Jsonable;

class BladeHelper
{
    public function createTimeTag($date, array $props = [])
    {
        // guard against null or empty strings (which are obviously invalid input)
        if (empty($date)) {
            return '';
        }

        if (is_string($date)) {
            $date = new Carbon($date);
        } else if (! ($date instanceOf Carbon) && ! ($date instanceOf IlluminateCarbon)) {
            throw new \Exception('Unsupported data type: '.get_class($date));
        }

        $html[] = '<time datetime="'.htmlentities($date->toIso8601String(), ENT_QUOTES).'"';
        foreach ($props as $attribute => $value) {
            $html[] = ' '.htmlentities($attribute, ENT_QUOTES).'="'.htmlentities($value, ENT_QUOTES).'"';
        }
        $html[] = '>';
        $html[] = htmlentities($date->diffForHumans(), ENT_QUOTES);
        $html[] = '</time>';

        return implode('', $html);
    }

    public function jsonSerialize($data, $pretty = false)
    {
        $options = $pretty ? JSON_PRETTY_PRINT : 0;
        $json = '';
        if ($data instanceOf Jsonable) {
            $json = $data->toJSON($options);
        } else {
            $tmp = json_encode($data, $options);
            if ($tmp !== false) {
                $json = $tmp;
            }
        }
        
        return htmlentities($json, ENT_QUOTES);
    }
}
