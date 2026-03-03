<?php

namespace App\Models;

class SearchDefinition extends ModelBase
{
    protected $table = 'search_definitions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'search_term', 'language_id', 'speech_ids', 'lexical_entry_group_ids'];

    public function viewEvents()
    {
        return $this->hasMany(SearchViewEvent::class, 'search_id', 'id');
    }
}
