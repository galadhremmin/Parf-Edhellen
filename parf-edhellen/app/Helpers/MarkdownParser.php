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
        $tagBegin = '[';
        $tagEnd   = ']';

        $word = '';
        $text = $Excerpt['text'];
        $length = strlen($text);

        // Examine the excerpt for reference tags. Regular expressions
        // isn't necessary in this case, as a simple iterative examination will
        // suffice, assuming that the Markdown parser has identified the correct
        // start tag.
        if ($length < 1) {
            return;
        }

        for ($i = 0; $i < $length; $i += 1) {
            if ($i < 2 && $text[$i] !== $tagBegin) {
                return; // erroneous
            }

            if ($i >= 2) {

                if ($text[$i] === $tagEnd) {
                    if ($i + 1 < $length && $text[$i + 1] === $tagEnd) {
                        break;
                    }

                    return; // erroneous
                } else {
                    $word .= $text[$i];
                }
            }
        }

        if (empty($word)) {
            return;
        }

        // Calculate the extent of the change, which would be the word, including
        // the start and end tags.
        $wordLength = strlen($tagBegin.$tagBegin . $word . $tagEnd.$tagEnd);

        // escape/encode special characters as their HTML equivalent
        $word = htmlspecialchars($word, ENT_QUOTES | ENT_HTML5);
        
        // remove footnotes, in case they were accidently imported
        $word = str_replace(['¹', '²', '³'], '', $word);

        return [
            'extent' => $wordLength,
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
