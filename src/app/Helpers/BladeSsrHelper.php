<?php

namespace App\Helpers;

use Exception;
use Illuminate\Contracts\Support\Jsonable;
use Spatie\Ssr\Renderer;

class BladeSsrHelper
{
    public function __construct(private ?Renderer $_renderer)
    { }

    public function render(string $appName, ?array $props = null, array $config = [ 'element' => 'div', 'attributes' => [] ])
    {
        if ($this->_renderer === null) {
            return '';
        }

        if (! isset($config['element']) || empty($config['element']) || ! is_string($config['element'])) {
            throw new Exception('SSR configuration incomplete: element is missing or malformed (expecting string).');
        }

        if (! isset($config['attributes']) || ! is_array($config['attributes'])) {
            $config['attributes'] = [];
        }

        $html = [
            '<'.$config['element']
        ];

        foreach ($config['attributes'] as $attribute => $value) {
            $html[] = ' '.$attribute.'="'.htmlentities($value, ENT_QUOTES).'"';
        }

        if (is_array($props)) {
            foreach ($props as $prop => $value) {
                $this->_renderer->context($prop, $value);

                if ($value instanceOf Jsonable) {
                    $value = $value->toJson();
                } else if (is_object($value) || is_array($value)) {
                    $value = json_encode($value);
                }

                $html[] = ' data-inject-prop-'.self::formatAsAttributeName($prop).'="'.htmlentities($value, ENT_QUOTES).'"';
            }
        }

        $html[] = ' data-inject-module="'.$appName.'" data-inject-mode="';
        $html[] = $this->_renderer->enabled() ? 'ssr' : 'async';
        $html[] = '">';
        $html[] = $this->_renderer->entry($appName)->render();
        $html[] = '</'.$config['element'].'>';

        return implode('', $html);
    }

    private static function formatAsAttributeName(string $attribute) {
        $attribute = str_replace('_', '-', $attribute);
        $attribute = preg_replace_callback('/([A-Z])/', function ($matches) {
            return '-'.strtolower($matches[1]);
        }, $attribute);
        return ltrim(strtolower($attribute), '_');
    }
}
