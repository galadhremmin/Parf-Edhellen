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
        return $this->fragment($fragment, [$fragmentIndex], $fragmentIndex, $previousFragment, $sentence);
    }

    protected function handleExcluded($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return $this->fragment($fragment, $fragment['fragment'], $fragmentIndex, $previousFragment, $sentence);
    }
    
    protected function handleParanthesisStart($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        $f = $fragment['fragment'];

        if (count($sentence) < 1 || $this->isParanthesisStart($previousFragment)) {
            return [$f];
        }

        return [' ', $f];
    }

    protected function handleParanthesisEnd($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return [$fragment['fragment']];
    }

    protected function finalizeSentence(array& $sentence)
    {
        // Noop
    }

    private function fragment($fragment, $mappingValue, int $fragmentIndex, $previousFragment, array $sentence)
    {
        if (count($sentence) < 1) {
            return [$mappingValue];
        }
        
        if ($previousFragment !== null && (
            $this->isConnection($previousFragment) || 
            $this->isParanthesisStart($previousFragment))) {
            return [$mappingValue];
        }

        return [' ', $mappingValue];
    }
}
