<?php

namespace App\Models;

/**
 * An irregular inflection and its base form, e.g. elves → elf.
 */
class WordNetException extends ModelBase
{
    protected $table = 'wordnet_exceptions';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['pos', 'form', 'base'];
}
