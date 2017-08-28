<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationReview extends ModelBase
{
    protected $fillable = [ 'translation_id' ];
    protected $dates = [
        'created_at',
        'updated_at',
        'date_reviewed'
    ];
    
    public function account() 
    {
        return $this->belongsTo(Account::class);
    }

    public function reviewed_by() 
    {
        return $this->belongsTo(Account::class, 'reviewed_by_account_id');
    }
}
