<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SentenceTranslation extends ModelBase
{
    use SoftDeletes;

    protected $fillable = [
        'sentence_id', 'paragraph_number', 'sentence_number', 'translation'
    ];
}
