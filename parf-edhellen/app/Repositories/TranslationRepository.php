<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Translation;
use App\Models\Keyword;

class TranslationRepository
{
    public function getKeywordsForLanguage(string $word, $reversed = false, $languageId = 0) 
    {
        self::formatWord($word);

        if ($languageId > 0) {
            $keywords = DB::table('translation as t')
                ->join('word as w', 't.WordID', '=', 'w.KeyID')
                ->where([
                    [ 't.Latest', '=', 1 ],
                    [ 't.Deleted', '=', 0 ],
                    [ 't.LanguageID', '=', $languageId ],
                    [ $reversed ? 'w.ReversedNormalizedKey' : 'w.NormalizedKey', 'like', $word ]
                ])
                ->orderBy('w.Key')
                ->select('w.Key as k', 'w.NormalizedKey as nk')
                ->get();
        } else {
            $keywords = Keyword::findByWord($word, $reversed)
                ->select('Keyword as k', 'NormalizedKeyword as nk')
                ->get();
        }

        return $keywords;
    }

    public function getWordTranslations($word) 
    {
        self::formatWord($word);

        $senses = self::getSensesForWord($word);
        return self::createTranslationQuery()
            ->whereIn('t.NamespaceID', $senses)
            ->get();
    }

    public function getTranslation($id) 
    {
        $id = intval($id);

        return self::createTranslationQuery()
            ->where('t.TranslationID', '=', $id)
            ->first();
    }

    protected static function createTranslationQuery() 
    {
        return DB::table('translation as t')
            ->join('word as w', 't.WordID', 'w.KeyID')
            ->leftJoin('auth_accounts as a', 't.AuthorID', 'a.AccountID')
            ->leftJoin('translation_group as tg', 't.TranslationGroupID', 'tg.TranslationGroupID')
            ->where([
                ['t.Latest', '=', 1],
                ['t.Deleted', '=', 0]
            ])
            ->select(
                'w.Key as Word', 't.TranslationID', 't.Translation', 't.Etymology', 't.Type', 't.Source',
                't.Comments', 't.Tengwar', 't.Phonetic', 't.NamespaceID', 't.LanguageID', 't.AuthorID',
                'a.Nickname as AuthorName', 'w.NormalizedKey', 't.Index', 't.DateCreated', 't.TranslationGroupID',
                'tg.Name as TranslationGroup', 'tg.Canon', 'tg.ExternalLinkFormat', 't.Uncertain', 't.ExternalID');
    }

    protected static function getSensesForWord($word) 
    {
        $q = DB::table('keywords as k')
            ->join('translation as t', 'k.TranslationID', '=', 't.TranslationID')
            ->where([
                ['k.NormalizedKeyword', 'like', $word],
                ['t.Deleted', '=', 0]
            ])
            ->whereNotNull('k.TranslationID')
            ->select('t.NamespaceID');

        $rows = DB::table('keywords')
            ->where('NormalizedKeyword', 'like', $word)
            ->whereNotNull('NamespaceID')
            ->select('NamespaceID')
            ->union($q)
            ->get();

        $ids = array();
        foreach ($rows as $row)
            $ids[] = $row->NamespaceID;

        return $ids;
    }

    protected static function formatWord(string& $word) 
    {
        if (strpos($word, '*') !== false) {
            $word = str_replace('*', '%', $word);
        } else {
            $word .= '%';
        }
    }
}