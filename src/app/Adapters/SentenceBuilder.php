<?php
namespace App\Adapters;

use Illuminate\Support\Collection;

abstract class SentenceBuilder
{
    protected $_fragments;
    protected $_numberOfFragments;

    public function __construct(array $fragments) 
    {
        $this->_fragments = $fragments;
        $this->_numberOfFragments = count($fragments);
    }

    public abstract function getName();

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
        $line = 0;
        $sentences = [];
        $sentence = [];
        $previousFragment = null;

        for ($i = 0; $i < $this->_numberOfFragments; $i += 1) {
            $fragment = $this->getFragment($i);
            $fragments = null;

            if ($this->isLineBreak($fragment)) {
                $this->finalizeSentence($sentence);

                $sentences[] = $sentence;
                $sentence = [];

            } else if ($this->isInterpunctuation($fragment)) {
                $fragments = $this->handleInterpunctuation($fragment, $i, $previousFragment, $sentence);

            }
            else if ($this->isConnection($fragment)) {
                $fragments = $this->handleConnection($fragment, $i, $previousFragment, $sentence);
            
            }
            else if ($this->isParanthesisStart($fragment)) {
                $fragments = $this->handleParanthesisStart($fragment, $i, $previousFragment, $sentence);

            }
            else if ($this->isParanthesisEnd($fragment)) {
                $fragments = $this->handleParanthesisEnd($fragment, $i, $previousFragment, $sentence);
            
            } else if ($this->isExcluded($fragment)) {
                $fragments = $this->handleExcluded($fragment, $i, $previousFragment, $sentence);

            } else {
                $fragments = $this->handleFragment($fragment, $i, $previousFragment, $sentence);

            }

            if ($fragments !== null) {
                foreach ($fragments as $newFragment) {
                    $sentence[] = $newFragment;
                }
            }

            $previousFragment = $fragment;
        }

        if (count($sentence)) {
            $sentences[] = $sentence;
        }

        return $sentences;
    }

    protected function getFragment(int $index) 
    {
        if ($index < 0 || $index >= $this->_numberOfFragments) {
            return null;
        }

        return $this->_fragments[$index];
    }

    protected function isLineBreak($fragment) 
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === 10;
    }

    protected function isInterpunctuation($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === 31;
    }

    protected function isConnection($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === 45;
    }

    protected function isParanthesisStart($fragment) 
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === 40;
    }

    protected function isParanthesisEnd($fragment) 
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === 41;
    }

    protected function isExcluded($fragment)
    {
        if (is_string($fragment)) {
            return false;
        }

        return $fragment['type'] === 24;
    }

    protected abstract function handleInterpunctuation($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function handleConnection($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function handleFragment($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function handleExcluded($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function handleParanthesisStart($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function handleParanthesisEnd($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function finalizeSentence(array& $sentence);
}