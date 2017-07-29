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

    protected abstract function handleInterpunctuation($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function handleConnection($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function handleFragment($fragment, int $fragmentIndex, $previousFragment, array $sentence);
    protected abstract function finalizeSentence(array& $sentence);
}