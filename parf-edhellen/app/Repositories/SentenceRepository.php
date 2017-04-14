<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SentenceRepository
{
    /**
     * Gets the languages for all available sentences.
     * @return mixed
     */
    public function getLanguages()
    {
        return DB::table('language as l')
            ->join('sentence as s', 'l.ID', '=', 's.LanguageID')
            ->select('l.Name', 'l.ID')
            ->distinct()
            ->get();
    }
}