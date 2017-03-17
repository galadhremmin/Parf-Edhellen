<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Sense extends Model
{
    protected $table = 'namespace';
    protected $primaryKey = 'NamespaceID';

    public function word() {
        return $this->hasOne(Word::class, 'KeyID', 'IdentifierID');
    }

    public function scopeFindForWord($query, $normalizedWord) {
        if (strpos($normalizedWord, '*') === false) {
            $normalizedWord .= '%';
        } else {
            $normalizedWord = str_replace('*', '%', $normalizedWord);
        }

        $peripheralNamespaces = DB::table('keywords as k')
            ->distinct()
            ->join('translation as t', 'k.TranslationID', '=', 't.TranslationID')
            ->where([
                ['k.NormalizedKeyword', 'like', $normalizedWord],
                ['t.Deleted', '=', 0]
            ])
            ->select('k.NamespaceID');


        return $query
            ->join('keywords', 'namespace.NamespaceID', 'keywords.NamespaceID')
            ->where('NormalizedKeyword', 'like', $normalizedWord)
            ->select('namespace.NamespaceID')
            ->union($peripheralNamespaces);

    }
}
