<?php

namespace App\Repositories\ValueObjects;

class SearchIndexSearchValue implements \JsonSerializable
{
    use Traits\CanInitialize;

    public function __construct(array $properties)
    {
        $this->initializeAll($properties, [
            'lexical_entry_group_ids', 'inflections', 'include_old', 'language_id', 'natural_language', 'reversed',
            'speech_ids', 'word',
        ], /* required: */ false);
    }

    public function getLexicalEntryGroupIds()
    {
        return $this->getValue('lexical_entry_group_ids');
    }

    public function getIncludesInflections()
    {
        $v = $this->getValue('inflections');

        return $v ? $v : false;
    }

    public function getIncludesOld()
    {
        $v = $this->getValue('include_old');

        return ! $v ? $v : true;
    }

    public function getLanguageId()
    {
        $v = $this->getValue('language_id');

        return $v ? $v : 0;
    }

    public function getNaturalLanguage()
    {
        $v = $this->getValue('natural_language');

        return $v ? $v : false;
    }

    public function getReversed()
    {
        $v = $this->getValue('reversed');

        return $v ? $v : false;
    }

    public function getSpeechIds()
    {
        return $this->getValue('speech_ids');
    }

    public function getWord()
    {
        return $this->getValue('word');
    }
}
