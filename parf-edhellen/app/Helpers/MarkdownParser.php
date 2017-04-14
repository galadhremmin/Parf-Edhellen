<?php

namespace App\Helpers;

class MarkdownParser extends \Parsedown
{

    function __construct($disabledBlockTypes = [])
    {
        $this->InlineTypes['['][] = 'Reference';
        $this->InlineTypes['>']   = ['SeeAlso'];

        foreach ($disabledBlockTypes as $disabledBlockType)
            unset($this->BlockTypes[$disabledBlockType]);
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

        // escape/encode special characters as their HTML equivalent
        $word = htmlspecialchars($matches[1], ENT_QUOTES | ENT_HTML5);
        // remove footnotes, in case they were accidently imported
        $word = str_replace(['¹', '²', '³'], '', $word);

        return [
            'extent' => strlen($matches[0]),
            'element' => [
                'name' => 'a',
                'handler' => 'line',
                'text' => $word,
                'attributes' => [
                    'href' => '/w/' . urlencode($word),
                    'title' => 'Navigate to '.$word.'.',
                    'class' => 'ed-word-reference',
                    'data-word' => StringHelper::normalize($word)
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
