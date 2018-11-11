<?php

namespace App\Repositories;

use Auth;
use Illuminate\Support\Facades\DB;

use App\Events\{
    SentenceCreated,
    SentenceEdited
};
use App\Models\{ 
    Sentence, 
    SentenceFragment,
    SentenceFragmentInflectionRel,
    Speech
};

class SentenceRepository
{
    private $_keywordRepository;

    public function __construct(KeywordRepository $keywordRepository)
    {
        $this->_keywordRepository = $keywordRepository;
    }

    /**
     * Gets the languages for all available sentences.
     * @return mixed
     */
    public function getLanguages()
    {
        return DB::table('languages as l')
            ->join('sentences as s', 'l.id', '=', 's.language_id')
            ->select('l.name', 'l.id', 'l.description')
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
    public function getInflectionsForGlosses(array $ids)
    {
        return DB::table('sentence_fragments as sf')
            ->join('speeches as sp', 'sf.speech_id', 'sp.id')
            ->join('sentences as s', 'sf.sentence_id', 's.id')
            ->join('languages as l', 's.language_id', 'l.id')
            ->leftJoin('sentence_fragment_inflection_rels as r', 'sf.id', 'r.sentence_fragment_id')
            ->leftJoin('inflections as i', 'r.inflection_id', 'i.id')
            ->whereIn('sf.gloss_id', $ids)
            ->select('sf.gloss_id', 'sf.fragment as word', 'i.name as inflection', 'sp.name as speech', 
                'sf.sentence_id', 'sf.id as sentence_fragment_id', 's.name as sentence_name', 'l.name as language_name',
                'l.id as language_id')
            ->orderBy('sf.fragment')
            ->get()
            ->groupBy('sentence_fragment_id')
            ->toArray();
    }

    public function getSentence(int $id)
    {
        $sentence = Sentence::findOrFail($id);
        $fragments = $sentence->sentence_fragments;
        $inflections = SentenceFragmentInflectionRel::whereIn('sentence_fragment_id', $fragments->map(function ($f) {
                return $f->id;
            }))
            ->join('inflections', 'inflections.id', 'inflection_id')
            ->select('sentence_fragment_id', 'inflections.name', 'inflections.id as inflection_id')
            ->get();
        $translations = $sentence->sentence_translations()
            ->select('sentence_number', 'translation')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->sentence_number => $item->translation];
            });

        $speechIds = $fragments->reduce(function ($carry, $f) {
            if ($f->speech_id !== null && !in_array($f->speech_id, $carry)) {
                $carry[] = $f->speech_id;
            }

            return $carry;
        }, []);
        $speeches = count($speechIds) < 1 ? [] : Speech::whereIn('id', $speechIds)
            ->select('id', 'name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->name];
            });
        
        $sentence->makeHidden(['sentence_translations', 'sentence_fragments']);
        $fragments = $fragments->groupBy('sentence_number');

        return [
            'sentence' => $sentence,
            'sentence_translations' => $translations,
            'sentence_fragments' => $fragments,
            'speeches' => $speeches
        ];
    }

    public function saveSentence(Sentence $sentence, array $fragments, array $inflections) 
    {
        $changed = !! $sentence->id;
        $numberOfFragments = count($fragments);
        if ($numberOfFragments !== count($inflections)) {
            throw new \Exception('The number of fragments must match the number of inflections.');
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

            $this->_keywordRepository->createKeyword($fragment->gloss->word, $fragment->gloss->sense, 
                $fragment->gloss, $fragment->fragment, $fragment->id);
        }

        // Inform listeners of this change.
        $event = ! $changed 
                ? new SentenceCreated($sentence, $sentence->account_id)
                : new SentenceEdited($sentence, Auth::user()->id);
        event($event);

        return $sentence;
    }

    public function destroyFragments(Sentence $sentence) 
    {
        foreach ($sentence->sentence_fragments as $fragment) {
            $fragment->inflection_associations()->delete();
            $fragment->keywords()->delete();
            $fragment->delete();
        }
    }
}
