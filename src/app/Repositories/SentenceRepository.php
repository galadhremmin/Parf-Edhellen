<?php

namespace App\Repositories;

use App\Models\{ AuditTrail, Sentence, SentenceFragment };
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
            ->orderBy('s.name')
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

    /**
     * Gets inflections for the specified IDs. Returns an associative array
     * keyed with the sentence fragment associated with the inflection.
     *
     * @param number[] $ids
     * @return array
     */
    public function getInflectionsForTranslations(array $ids)
    {
        return DB::table('sentence_fragments as sf')
            ->join('speeches as sp', 'sf.speech_id', 'sp.id')
            ->join('sentences as s', 'sf.sentence_id', 's.id')
            ->join('languages as l', 's.language_id', 'l.id')
            ->leftJoin('sentence_fragment_inflection_rels as r', 'sf.id', 'r.sentence_fragment_id')
            ->leftJoin('inflections as i', 'r.inflection_id', 'i.id')
            ->whereIn('sf.translation_id', $ids)
            ->select('sf.translation_id', 'sf.fragment as word', 'i.name as inflection', 'sp.name as speech', 
                'sf.sentence_id', 'sf.id as sentence_fragment_id', 's.name as sentence_name', 'l.name as language_name',
                'l.id as language_id')
            ->orderBy('sf.fragment')
            ->get()
            ->groupBy('sentence_fragment_id')
            ->toArray();
    }

    public function saveSentence(Sentence $sentence, array $fragments, array $inflections) 
    {
        $changed = $sentence->id !== 0;
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

        // Register an audit trail
        AuditTrail::create([
            'account_id'        => $sentence->account_id,
            'entity_id'         => $sentence->id,
            'entity_context_id' => AuditTrail::CONTEXT_SENTENCE,
            'action_id'         => $changed 
                ? AuditTrail::ACTION_SENTENCE_EDIT 
                : AuditTrail::ACTION_SENTENCE_ADD
        ]);
    }

    public function destroyFragments(Sentence $sentence) 
    {
        foreach ($sentence->sentence_fragments as $fragment) {
            $fragment->inflection_associations()->delete();
            $fragment->delete();
        }
    }
}