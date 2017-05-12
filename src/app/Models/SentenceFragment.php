<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceFragment extends Model
{
    protected $fillable = [ 'translation_id' ];
    
    public function sentence() 
    {
        return $this->belongsTo(Sentence::class);
    }

    public function speech()
    {
        return $this->belongsTo(Speech::class);
    }

    public function inflection_associations()
    {
        return $this->hasMany(SentenceFragmentInflectionRel::class);
    }

    public function isPunctuationOrWhitespace() 
    {
        return $this->is_linebreak || preg_match('/^[·,\\.!\\?\\n\\s\\-]$/u', $this->fragment);
    }

    public function isDot() 
    {
        return preg_match('/^[·\\-]$/u', $this->fragment);
    }
}
