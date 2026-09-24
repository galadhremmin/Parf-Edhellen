<?php

namespace App\Models;

class WordNetHypernym extends ModelBase
{
    protected $table = 'wordnet_hypernyms';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['synset_id', 'hypernym_id', 'is_instance'];

    protected $casts = [
        'is_instance' => 'boolean',
    ];
}
