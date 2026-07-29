<?php

namespace App\Models;

class LexicalEntryDerivationData extends ModelBase
{
    protected $table = 'lexical_entry_derivation_data';

    protected $primaryKey = 'lexical_entry_id';

    public $incrementing = false;

    protected $keyType = 'int';

    const CREATED_AT = null;

    protected $fillable = ['lexical_entry_id', 'derivations', 'derivatives', 'phonetic_developments', 'updated_at'];

    protected $casts = [
        'derivations' => 'array',
        'derivatives' => 'array',
        'phonetic_developments' => 'array',
    ];
}
