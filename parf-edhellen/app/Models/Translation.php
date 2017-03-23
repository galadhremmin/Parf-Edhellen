<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = 'translation';
    protected $primaryKey = 'TranslationID';
    protected $dates = [ 'DateCreated' ];

    public function author() 
    {
        return $this->hasOne(Author::class, 'AccountID', 'AuthorID');
    }

    public function group() 
    {
        return $this->hasOne(TranslationGroup::class, 'TranslationGroupID', 'TranslationGroupID');
    }
    
    public function language() 
    {
        return $this->hasOne(Language::class, 'ID', 'LanguageID');
    }

    public function word() 
    {
        return $this->hasOne(Word::class, 'KeyID', 'WordID');
    }
}
