<?php

namespace App\Repositories\ValueObjects;

class ExternalEntitySearchValue extends SearchIndexSearchValue implements \JsonSerializable
{
    use Traits\CanInitialize;

    public function __construct(array $properties)
    {
        parent::__construct($properties);
        $this->initialize($properties, 'external_id', true);
        $this->initialize($properties, 'lexical_entry_group_id', false);
    }

    public function getExternalId()
    {
        return $this->getValue('external_id');
    }

    public function getLexicalEntryGroupId()
    {
        return $this->getValue('lexical_entry_group_id') ?? 0;
    }
}
