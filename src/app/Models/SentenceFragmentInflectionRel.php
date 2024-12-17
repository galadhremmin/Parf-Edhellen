<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SentenceFragmentInflectionRel extends ModelBase
{
    use SoftDeletes;

    protected $fillable = [ 'sentence_fragment_id', 'inflection_id' ];
    protected $hidden = [ 'created_at', 'updated_at' ];

    public function sentence_fragment()
    {
        return $this->belongsTo(SentenceFragment::class);
    }

    public function inflection()
    {
        return $this->hasOne(Inflection::class);
    }
}
