<?php

namespace App\Models;

class ConceptLabel extends ModelBase
{
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['term_key', 'concept_id'];
}
