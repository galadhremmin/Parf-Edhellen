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
        return DB::table('languages as l')
            ->join('sentences as s', 'l.id', '=', 's.language_id')
            ->select('l.name', 'l.id')
            ->distinct()
            ->get();
    }

    /**
     * Gets sentences for the specified language.
     * @return mixed
     */
    public function getByLanguage(int $languageId)
    {
        return DB::table('sentences as s')
            ->leftJoin('accounts as a', 's.account_id', '=', 'a.id')
            ->where('s.is_approved', 1)
            ->where('s.language_id', $languageId)
            ->select('s.id', 's.description', 's.source', 's.is_neologism', 's.account_id',
                'a.nickname as account_name', 's.name')
            ->get();
    }

    public function getAllGroupedByLanguage()
    {
        return DB::table('sentences as s')
            ->join('languages as l', 's.language_id', 'l.id')
            ->leftJoin('accounts as a', 's.account_id', '=', 'a.id')
            ->where('s.is_approved', 1)
            ->select('s.id', 's.description', 's.source', 's.is_neologism', 's.account_id',
                'a.nickname as account_name', 's.name', 'l.name as language_name')
            ->get()
            ->groupBy('language_name');
    }

    public function saveSentence(Sentence $sentence, array $fragments, array $inflections) 
    {
        $numberOfFragments = count($fragments);
        if ($numberOfFragments !== count($inflections)) {
            throw new Exception('The number of fragments must match the number of inflections.');
        }

        $sentence->save();

        $this->destroyFragments($sentence);
        
        for ($i = 0; $i < $numberOfFragments; $i += 1) {
            $fragment = $fragments[$i];
            $fragment->sentence_id = $sentence->id;
            $fragment->save();

            for ($j = 0; $j < count($inflections[$i]); $j += 1) {
                $inflectionRel = $inflections[$i][$j];
                $inflectionRel->sentence_fragment_id = $fragment->id;
                $inflectionRel->save(); 
            }
        }
    }

    public function destroyFragments(Sentence $sentence) 
    {
        foreach ($sentence->sentence_fragments as $fragment) {
            $fragment->inflection_associations()->delete();
            $fragment->delete();
        }
    }
}