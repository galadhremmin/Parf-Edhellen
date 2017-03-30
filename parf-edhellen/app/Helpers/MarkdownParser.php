<?php

namespace App\Helpers;

class MarkdownParser extends \Parsedown
{

    function __construct()
    {
        $this->InlineTypes['['][] = 'Reference';
        $this->InlineTypes['>']   = ['SeeAlso'];

        unset($this->BlockTypes['>']); // blockquote isn't supported!
    }

    /**
     * Implements [[references]]
     * @param $Excerpt
     * @return array|void
     */
    protected function inlineReference($Excerpt)
    {
        $context = $Excerpt['context'];
        if (empty($context))
            return;

        $matches = null;
        if (!preg_match("/\\[\\[([^\\]]+)\\]\\]/", $context, $matches))
            return;

        $word = htmlspecialchars($matches[1], ENT_QUOTES | ENT_HTML5);
        return [
            'extent' => strlen($matches[0]),
            'element' => [
                'name' => 'a',
                'handler' => 'line',
                'text' => $word,
                'attributes' => [
                    'href' => '/w/' . urlencode($word),
                    'title' => 'Navigate to '.$word.'.'
                ]
            ]
        ];
    }

    /**
     * Implements >> "see also"-tokens
     * @param $Excerpt
     */
    protected function inlineSeeAlso($Excerpt)
    {
        $text = $Excerpt['text'];
        if ($text[0] === '>' && $text[1] === '>')
        {
            return [
                'extent' => 2,
                'element' => [
                    'name' => 'span',
                    'text' => '',
                    'attributes' => [
                        'class' => 'glyphicon glyphicon-hand-right'
                    ]
                ]
            ];
        }
    }
}
