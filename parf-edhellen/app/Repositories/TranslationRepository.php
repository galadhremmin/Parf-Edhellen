<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Translation;

class TranslationRepository
{
    public function getWordTranslations($word) {
        if (strpos($word, '*') === false) {
            $word .= '%';
        } else {
            $word = str_replace('*', '%', $word);
        }

        $senses = $this->getSensesForWord($word);
        return $this->createTranslationQuery()
            ->whereIn('t.NamespaceID', $senses)
            ->get();
    }

    public function getTranslation($id) {
        $id = intval($id);

        return $this->createTranslationQuery()
            ->where('t.TranslationID', '=', $id)
            ->first();
    }

    protected function createTranslationQuery()
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

    protected function getSensesForWord($word) {

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
}