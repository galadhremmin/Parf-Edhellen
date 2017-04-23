<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Keyword;

class TranslationRepository
{
    public function getKeywordsForLanguage(string $word, $reversed = false, $languageId = 0) 
    {
        self::formatWord($word);

        if ($languageId > 0) {
            $keywords = DB::table('translations as t')
                ->join('words as w', 't.word_id', '=', 'w.id')
                ->where([
                    [ 't.is_latest', '=', 1 ],
                    [ 't.is_deleted', '=', 0 ],
                    [ 't.language_id', '=', $languageId ],
                    [ $reversed ? 'w.reversed_normalized_word' : 'w.normalized_word', 'like', $word ]
                ])
                ->orderBy('w.word')
                ->select('w.word as k', 'w.normalized_word as nk')
                ->distinct();
        } else {
            $keywords = Keyword::findByWord($word, $reversed)
                ->select('keywords as k', 'normalized_keyword as nk');
        }

        return $keywords
            ->limit(100)
            ->get();
    }

    public function getWordTranslations(string $word) 
    {
        $senses = self::getSensesForWord($word);
        return self::createTranslationQuery()
            ->whereIn('t.sense_id', $senses)
            ->orderBy('word')
            ->get()
            ->toArray();
    }

    public function getTranslation(int $id) 
    {
        return self::createTranslationQuery()
            ->where('t.id', $id)
            ->first();
    }

    protected static function createTranslationQuery($latest = true) 
    {
        return DB::table('translations as t')
            ->join('words as w', 't.word_id', 'w.id')
            ->leftJoin('accounts as a', 't.account_id', 'a.id')
            ->leftJoin('translation_groups as tg', 't.translation_group_id', 'tg.id')
            ->where([
                ['t.is_latest', '=', $latest ? 1 : 0],
                ['t.is_deleted', '=', 0],
                ['t.is_index', '=', 0]
            ])
            ->select(
                'w.word', 't.id', 't.translation', 't.etymology', 't.type', 't.source',
                't.comments', 't.tengwar', 't.phonetic', 't.language_id', 't.account_id',
                'a.nickname as account_name', 'w.normalized_word', 't.is_index', 't.created_at', 't.translation_group_id',
                'tg.name as translation_group_name', 'tg.is_canon', 'tg.external_link_format', 't.is_uncertain', 't.external_id',
                't.is_latest');
    }

    protected static function getSensesForWord(string $word) 
    {
        $rows = DB::table('keywords')
            ->where('normalized_keyword', $word)
            ->select('sense_id')
            ->distinct()
            ->get();

        $ids = array();
        foreach ($rows as $row)
            $ids[] = $row->SenseID;

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