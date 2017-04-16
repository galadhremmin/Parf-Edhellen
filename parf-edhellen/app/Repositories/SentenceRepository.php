<?php

namespace App\Repositories;

use App\Models\Sentence;
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

    public function getByLanguage(int $languageId)
    {
        return DB::table('sentence as s')
            ->leftJoin('auth_accounts as a', 's.AuthorID', '=', 'a.AccountID')
            ->where('s.Approved', 1)
            ->select('s.SentenceID', 's.Description', 's.Source', 's.Neologism', 's.AuthorID',
                'a.Nickname as AuthorName', 's.Name')
            ->get();
    }

}