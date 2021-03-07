<?php

namespace App\Repositories\ValueObjects;

class SearchIndexSearchValue implements \JsonSerializable 
{
    use Traits\CanInitialize;

    public function __construct(array $properties)
    {
        $this->initializeAll($properties, [
            'gloss_group_ids', 'include_old', 'language_id', 'reversed',
            'speech_ids', 'word'
        ], /* required: */ false);
    }

    public function getGlossGroupIds() 
    {
        return $this->getValue('gloss_group_ids');
    }

    public function getIncludesOld() 
    {
        return $this->getValue('include_old');
    }

    public function getLanguageId() 
    {
        return $this->getValue('language_id');
    }

    public function getReversed() 
    {
        return $this->getValue('reversed');
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
