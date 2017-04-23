<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationReview extends Model
{
    protected $table = 'translation_review';
    protected $primaryKey = 'TranslationID';
    protected $dates = [ 'DateCreated', 'Reviewed' ];

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;
}
