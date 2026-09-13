<?php

namespace App\Helpers\SentenceBuilders;

class TengwarSentenceBuilder extends SentenceBuilder
{
    public function getName()
    {
        return 'tengwar';
    }

    protected function handleInterpunctuation($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        $fragment = $this->getFragment($fragmentIndex);

        return [' ', [$fragmentIndex, $this->tengwar($fragment)]];
    }

    protected function handleConnection($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return [];
    }

    protected function handleFragment($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return $this->fragment($fragment, [$fragmentIndex, $this->tengwar($fragment)], $fragmentIndex, $previousFragment, $sentence);
    }

    protected function handleExcluded($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return $this->fragment($fragment, $this->tengwar($fragment), $fragmentIndex, $previousFragment, $sentence);
    }

    protected function handleParanthesisStart($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        if (count($sentence) < 1 || $this->isParanthesisStart($previousFragment)) {
            return [$this->tengwar($fragment)];
        }

        return [' ', $this->tengwar($fragment)];
    }

    protected function handleParanthesisEnd($fragment, int $fragmentIndex, $previousFragment, array $sentence)
    {
        return [$this->tengwar($fragment)];
    }

    protected function finalizeParagraph(array &$sentence)
    {
        // Noop
    }

    /**
     * A fragment without a transcription contributes nothing to the tengwar line. Null reaches
     * the reader as a mapping it cannot resolve, so it has to be an empty string.
     */
    private function tengwar($fragment): string
    {
        return (string) ($fragment['tengwar'] ?? '');
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
