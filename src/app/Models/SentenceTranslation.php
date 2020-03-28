<?php

namespace App\Models;

class SentenceTranslation extends ModelBase
{
    protected $fillable = [
        'sentence_id', 'paragraph_number', 'sentence_number', 'translation'
    ];
}
