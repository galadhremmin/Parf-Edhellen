<?php

namespace App\Adapters;

class LatinSentenceBuilder extends SentenceBuilder
{
    public function getName()
    {
        return 'latin';
    }

    protected function handleInterpunctuation($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return [[$fragmentIndex]];
    }

    protected function handleConnection($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return [$fragment['fragment']];
    }

    protected function handleFragment($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        if (count($sentence) < 1) {
            return [[$fragmentIndex]];
        }
        
        if ($previousFragment !== null && $this->isConnection($previousFragment)) {
            return [[$fragmentIndex]];
        }

        return [' ', [$fragmentIndex]];
    }

    protected function finalizeSentence(array& $sentence)
    {
        // Noop
    }
}