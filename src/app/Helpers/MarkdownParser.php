<?php

namespace App\Helpers;

class MarkdownParser extends \Parsedown
{

    function __construct($disabledBlockTypes = [])
    {
        $this->InlineTypes['['][] = 'Reference';
        $this->InlineTypes['>']   = ['SeeAlso'];
        $this->InlineTypes['@']   = ['Transcription'];

        $this->inlineMarkerList = '!"*_&[:<>`~\\@';

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

    protected function blockTableComplete(array $Block = null)
    {
        $Block['element'] = [
            'name' => 'div',
            'handler' => 'element',
            'attributes' => [
                'class' => 'table-responsive'
            ],
            'text' => $Block['element']
        ];
        return $Block;
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

        // Extract language, if present
        $pos = strpos($word, '|');
        $language = '';
        if ($pos !== false) {
            $language = substr($word, 0, $pos);
            $word = substr($word, $pos + 1);
        }

        // escape/encode special characters as their HTML equivalent
        $word = htmlspecialchars($word, ENT_QUOTES | ENT_HTML5);

        // remove footnotes, in case they were  imported by accident
        $word = str_replace(['¹', '²', '³'], '', $word);
        $normalizedWord = StringHelper::normalize($word);

        $attrs = [
            'href' => '/w/' . urlencode($normalizedWord),
            'title' => 'Navigate to '.$word.'.',
            'class' => 'ed-word-reference',
            'data-word' => $normalizedWord
        ];
        if (! empty($language)) {
            $attrs['href'] .= '/'.$language;
            $attrs['data-language-short-name'] = $language;
        }

        return [
            'extent' => $wordLength,
            'element' => [
                'name' => 'a',
                'handler' => 'line',
                'text' => $word,
                'attributes' => $attrs
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

    protected function inlineTranscription($Excerpt)
    {
        $text = $Excerpt['text'];
        $pos = strpos($text, '@', 1);
        if ($pos === false) {
            return;
        }
        $extent = $pos + 1;
        $text = substr($text, 1, $pos - 1);

        $pos = strpos($text, '|');
        if ($pos === false) {
            return;
        }

        $mode = trim( substr($text, 0, $pos) );
        if (empty($mode)) {
            return;
        }

        $body = trim( substr($text, $pos + 1) );
        if (empty($body)) {
            return;
        }
        
        return [
            'extent' => $extent,
            'element' => [
                'name' => 'span',
                'text' => $body,
                'attributes' => [
                    'class' => 'tengwar',
                    'data-tengwar-transcribe' => 'true',
                    'data-tengwar-mode' => $mode
                ]
            ]
        ];
    }

    protected function inlineLink($Excerpt)
    {
        $link = parent::inlineLink($Excerpt);
        return $this->shortenUri($link);
    }

    protected function inlineUrl($Excerpt)
    {
        $link = parent::inlineUrl($Excerpt);
        return $this->shortenUri($link);
    }

    private function shortenUri($link)
    {
        if (! is_array($link)) {
            return;
        }
        
        $attrs =& $link['element']['attributes'];
        $uri   = $attrs['href'];
        $text  = $link['element']['text'];
        if (! filter_var($uri, FILTER_VALIDATE_URL) ||
            ! filter_var($text, FILTER_VALIDATE_URL)) {
            return $link;
        }

        $parts = parse_url($uri);
        if (! isset($parts['host']) || empty($parts['host'])) {
            return $link;
        }

        $link['element']['text'] = $parts['host'];
        $attrs['title'] = 'Goes to: '.$uri;
        
        return $link;
    }
}
