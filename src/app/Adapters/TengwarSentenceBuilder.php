<?php

namespace App\Adapters;

class TengwarSentenceBuilder extends SentenceBuilder
{
    public function getName()
    {
        return 'tengwar';
    }

    protected function handleInterpunctuation($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        $fragment = $this->getFragment($fragmentIndex);
        return [' ', [$fragmentIndex, $fragment['tengwar']]];
    }

    protected function handleConnection($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return [];
    }

    protected function handleFragment($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return $this->fragment($fragment, [$fragmentIndex, $fragment['tengwar']], $fragmentIndex, $previousFragment, $sentence);
    }

    protected function handleExcluded($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return $this->fragment($fragment, $fragment['tengwar'], $fragmentIndex, $previousFragment, $sentence);
    }
    
    protected function handleParanthesisStart($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        if (count($sentence) < 1 || $this->isParanthesisStart($previousFragment)) {
            return [$fragment['tengwar']];
        }

        return [' ', $fragment['tengwar']];
    }

    protected function handleParanthesisEnd($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return [$fragment['tengwar']];
    }

    protected function finalizeSentence(array& $sentence)
    {
        // Noop
    }

    private function fragment($fragment, $mappingValue, int $fragmentIndex, $previousFragment, array $sentence)
    {
        if (count($sentence) < 1 || (
            $this->isConnection($previousFragment) ||
            $this->isParanthesisStart($previousFragment))) {
            return [$mappingValue];
        }

        return [' ', $mappingValue];
    }
}
