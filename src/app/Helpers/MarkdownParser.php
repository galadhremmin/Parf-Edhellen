<?php

namespace App\Helpers;

class MarkdownParser extends \Parsedown
{

    function __construct($disabledBlockTypes = [])
    {
        $this->InlineTypes['['][] = 'Reference';
        $this->InlineTypes['>']   = ['SeeAlso'];

        // escapes markup (HTML)
        $this->setMarkupEscaped(true);

        foreach ($disabledBlockTypes as $disabledBlockType)
            unset($this->BlockTypes[$disabledBlockType]);
    }

    /**
     * Overrides the original elements method, which combines all elements into a HTML string.
     * This method is overridden to remove excessive new lines being inserted before every element. 
     *
     * @param array $Elements
     * @return void
     */
    protected function elements(array $Elements)
    {
        $markup = '';

        foreach ($Elements as $Element)
        {
            $markup .= $this->element($Element);
        }

        return $markup;
    }

    /**
     * Adds bootstrap classes to the table element.
     * 
     */
    protected function blockTable($Line, array $Block = null)
    {
        $table = parent::blockTable($Line, $Block);

        if (! $table || ! isset($table['element'])) {
            return $table;
        }
        
        if (! isset($table['element']['attributes'])) {
            $table['element']['attributes'] = [];
        }

        $table['element']['attributes']['class'] = "table table-condensed table-striped table-hover";

        return $table;
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

        // remove footnotes, in case they were  imported by accident
        $word = str_replace(['¹', '²', '³'], '', $word);
        $normalizedWord = StringHelper::normalize($word);

        return [
            'extent' => $wordLength,
            'element' => [
                'name' => 'a',
                'handler' => 'line',
                'text' => $word,
                'attributes' => [
                    'href' => '/w/' . urlencode($normalizedWord),
                    'title' => 'Navigate to '.$word.'.',
                    'class' => 'ed-word-reference',
                    'data-word' => $normalizedWord
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
        if (strlen($text) >= 2 && $text[0] === '>' && $text[1] === '>')
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
