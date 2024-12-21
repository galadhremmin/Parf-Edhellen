<?php

namespace App\Helpers\SentenceBuilders;

use Illuminate\Support\Collection;

abstract class SentenceBuilder
{
    protected Collection $_fragments;

    protected int $_numberOfFragments;

    const TYPE_CODE_WORD = 0;

    const TYPE_CODE_NEWLINE = 10;

    const TYPE_CODE_EXCLUDE = 24;

    const TYPE_CODE_INTERPUNCTUATION = 31;

    const TYPE_CODE_OPEN_PARANTHESIS = 40;

    const TYPE_CODE_CLOSE_PARANTHESIS = 41;

    const TYPE_CODE_WORD_CONNEXION = 45;

    public function __construct(Collection $fragments)
    {
        $this->_fragments = $fragments;
        $this->_numberOfFragments = count($fragments);
    }

    abstract public function getName();

    /**
     * Builds a mapping array based on the fragments associated with the instance of this class.
     * The mapping array is a multi-dimensional array arranged in the following manner:
     *
     * 0th: line/paragraph
     *
     * 1st: fragment - can either be textual (such as space ' ') or an array:
     *
     * 2nd: zero-based index referring to an element of the array of fragments. If the
     *      array contains a second element (at 1st position), the value will override
     *      the referenced fragment's text value when rendering.
     *
     * To build a sentence out of the mapping array, one must therefore check whether the
     * 1st position is an array, or a string. Example:
     *
     *       foreach ($mapping as $paragraph) {
     *         $str[] = '<p>';
     *         foreach ($paragraph as $fragmentMap) {
     *           if (is_array($fragmentMap)) {
     *             $sentence[] = count($fragmentMap) > 1 ? $fragmentMap[1] : $fragments[$fragmentMap[0]];
     *           } else {
     *             $sentence[] = $fragmentMap; // textual
     *           }
     *         }
     *         $str[] = '</p>';
     *       }
     *
     * @return array
     */
    public function build()
    {
        $paragraphs = [];
        $sentence = [];
        $previousFragment = null;

        for ($i = 0; $i < $this->_numberOfFragments; $i += 1) {
            $fragment = $this->getFragment($i);
            $nextFragment = $this->getFragment($i + 1);
            $fragments = null;

            if ($nextFragment != null && $nextFragment->paragraph_number !== $fragment->paragraph_number) {

                if (! $this->isLineBreak($fragment)) {
                    $fragments = $this->handleFragment($fragment, $i, $previousFragment, $sentence);
                    $this->applyFragments($sentence, $fragments);
                }

                $this->finalizeParagraph($sentence);

                $paragraphs[$fragment->paragraph_number] = $sentence;
                $sentence = [];

            } elseif ($this->isInterpunctuation($fragment)) {
                $fragments = $this->handleInterpunctuation($fragment, $i, $previousFragment, $sentence);

            } elseif ($this->isConnection($fragment)) {
                $fragments = $this->handleConnection($fragment, $i, $previousFragment, $sentence);

            } elseif ($this->isParanthesisStart($fragment)) {
                $fragments = $this->handleParanthesisStart($fragment, $i, $previousFragment, $sentence);

            } elseif ($this->isParanthesisEnd($fragment)) {
                $fragments = $this->handleParanthesisEnd($fragment, $i, $previousFragment, $sentence);

            } elseif ($this->isExcluded($fragment)) {
                $fragments = $this->handleExcluded($fragment, $i, $previousFragment, $sentence);

            } else {
                $fragments = $this->handleFragment($fragment, $i, $previousFragment, $sentence);

            }

            if ($fragments !== null) {
                $this->applyFragments($sentence, $fragments);
            }

            $previousFragment = $fragment;
        }

        if (count($sentence)) {
            $paragraphs[$previousFragment->paragraph_number] = $sentence;
        }

        return $paragraphs;
    }

    public function getFragment(int $index)
    {
        if ($index < 0 || $index >= $this->_numberOfFragments) {
            return null;
        }

        return $this->_fragments[$index];
    }

    public function isLineBreak($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === self::TYPE_CODE_NEWLINE;
    }

    public function isInterpunctuation($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === self::TYPE_CODE_INTERPUNCTUATION;
    }

    public function isConnection($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === self::TYPE_CODE_WORD_CONNEXION;
    }

    public function isParanthesisStart($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === self::TYPE_CODE_OPEN_PARANTHESIS;
    }

    public function isParanthesisEnd($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === self::TYPE_CODE_CLOSE_PARANTHESIS;
    }

    public function isExcluded($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === self::TYPE_CODE_EXCLUDE;
    }

    public function applyFragments(array &$sentence, array $fragments)
    {
        foreach ($fragments as $newFragment) {
            $sentence[] = $newFragment;
        }
    }

    abstract protected function handleInterpunctuation($fragment, int $fragmentIndex, $previousFragment, array $sentence);

    abstract protected function handleConnection($fragment, int $fragmentIndex, $previousFragment, array $sentence);

    abstract protected function handleFragment($fragment, int $fragmentIndex, $previousFragment, array $sentence);

    abstract protected function handleExcluded($fragment, int $fragmentIndex, $previousFragment, array $sentence);

    abstract protected function handleParanthesisStart($fragment, int $fragmentIndex, $previousFragment, array $sentence);

    abstract protected function handleParanthesisEnd($fragment, int $fragmentIndex, $previousFragment, array $sentence);

    abstract protected function finalizeParagraph(array &$sentence);
}
