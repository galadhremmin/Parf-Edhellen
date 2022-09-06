<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\AuthManager;

use App\Events\{
    SentenceCreated,
    SentenceEdited,
    SentenceFragmentsDestroyed
};
use App\Models\{
    Gloss,
    GlossInflection,
    Keyword,
    Sentence,
    Inflection,
};
use App\Helpers\{
    SentenceHelper,
    StringHelper
};
use Illuminate\Support\Collection;

class SentenceRepository
{
    private $_glossInflectionRepository;
    private $_keywordRepository;
    /**
     * @var AuthManager
     */
    private $_authManager;

    public function __construct(GlossInflectionRepository $glossInflectionRepository, KeywordRepository $keywordRepository, AuthManager $authManager)
    {
        $this->_glossInflectionRepository = $glossInflectionRepository;
        $this->_keywordRepository = $keywordRepository;
        $this->_authManager = $authManager;
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
            ->orderBy('language_name')
            ->orderBy('name')
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
            ->join('sentences as s', 'sf.sentence_id', 's.id')
            ->join('gloss_inflections as gi', 'sf.id', 'gi.sentence_fragment_id')
            ->join('speeches as sp', 'gi.speech_id', 'sp.id')
            ->join('languages as l', 'gi.language_id', 'l.id')
            ->join('inflections as i', 'gi.inflection_id', 'i.id')
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
        $sentence = Sentence::with('account', 'language')->find($id);
        if ($sentence == null) {
            return $sentence;
        }

        $fragments = $sentence->sentence_fragments()->with(['gloss_inflections', 'speech'])->get();
        $translations = $sentence->sentence_translations()
            ->select('sentence_number', 'paragraph_number', 'translation')
            ->get()
            ->transform(function ($item) {
                $item->makeHidden('paragraph_number');
                return $item;
            })->mapWithKeys(function ($item) {
                return [ $item->paragraph_number => $item ];
            });

        $sentence->makeHidden(['account_id', 'language_id', 'sentence_translations', 'sentence_fragments']);

        return [
            'sentence' => $sentence,
            'sentence_fragments' => $fragments,
            'sentence_translations' => $translations,
            'sentence_transformations' => resolve(SentenceHelper::class)->buildSentences($fragments),
            'speeches' => $this->getSpeechesForSentenceFragments($fragments),
            'inflections' => $this->getInflectionsForSentenceFragments($fragments)
        ];
    }

    public function getSpeechesForSentenceFragments(Collection $fragments)
    {
        $speeches = $fragments->map(function ($f) {
            return $f->speech;
        }) //
            ->whereNotNull() //
            ->unique() //
            ->keyBy('id');

        return $speeches;
    }

    public function getInflectionsForSentenceFragments(Collection $fragments)
    {
        $inflections = Inflection::whereIn('id', $fragments->map(function ($f) {
            return $f->gloss_inflections->map(function ($i) {
                return $i->inflection_id;
            });
        })->flatten()) //
            ->get() //
            ->keyBy('id');

        return $inflections;
    }

    public function saveSentence(Sentence $sentence, array $fragments, array $inflectionsPerFragments, array $translations = []) 
    {
        $changed = !! $sentence->id;
        $numberOfFragments = count($fragments);
        if ($numberOfFragments !== count($inflectionsPerFragments)) {
            throw new \Exception('The number of fragments must match the number of inflections.');
        }
        
        try {
            DB::beginTransaction();
            $sentence->save();

            // Re-create all sentence fragments
            $this->destroyFragments($sentence);
            $sentence->sentence_fragments()->saveMany($fragments);

            // Re-create all sentence translations
            $sentence->sentence_translations()->delete();
            if (count($translations) > 0) {
                $sentence->sentence_translations()->saveMany($translations);
            }

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

        $sentence->refresh();

        $i = 0;
        foreach ($sentence->sentence_fragments as $fragment) {
            $inflections = $inflectionsPerFragments[$i++];

            if (! empty($inflections)) {
                foreach ($inflections as $inflection) {
                    $inflection->speech_id            = $fragment->speech_id;
                    $inflection->gloss_id             = $fragment->gloss_id;
                    $inflection->language_id          = $sentence->language_id;
                    $inflection->account_id           = $sentence->account_id;
                    $inflection->sentence_id          = $fragment->sentence_id;
                    $inflection->sentence_fragment_id = $fragment->id;
                    $inflection->word                 = $fragment->fragment;
                }

                $this->_glossInflectionRepository->saveInflectionAsOneGroup(collect($inflections));
            }
        }

        // Inform listeners of this change.
        $event = ! $changed 
                ? new SentenceCreated($sentence, $sentence->account_id)
                : new SentenceEdited($sentence, $this->_authManager->user()->id);
        event($event);

        return $sentence;
    }

    public function destroyFragments(Sentence $sentence) 
    {
        $fragments = $sentence->sentence_fragments;
        foreach ($fragments as $fragment) {
            $fragment->gloss_inflections()->delete();
            $fragment->keywords()->delete();
        }
        $sentence->sentence_fragments()->delete();

        event(new SentenceFragmentsDestroyed($fragments));
    }

    public function suggestFragmentGlosses(Collection $fragments, int $languageId)
    {
        $distinctFragments = $fragments->filter(function ($f) {
            return $f->type === 0 && $f->gloss_id === 0; // = i.e. words
        })->map(function ($f) {
            return [
                'normalized' => StringHelper::normalize($f->fragment, true),
                'original'   => StringHelper::toLower($f->fragment)
            ];
        })->unique('normalized');

        $maximumFragments = config('ed.sentence_repository_maximum_fragments');
        if ($distinctFragments->count() > $maximumFragments) {
            $distinctFragments->splice(0, $maximumFragments);
        }

        $suggestions = [];
        foreach ($distinctFragments as $f) {
            $inflectionIds = [];
            $glossId = null;
            $speechId = null;

            $fragmentData = Keyword::where('normalized_keyword', $f['normalized'])
                ->whereNotNull('sentence_fragment_id')
                ->join('sentence_fragments', 'sentence_fragments.id', '=', 'keywords.sentence_fragment_id')
                ->where('is_sense', 0)
                ->select('sentence_fragment_id', 'speech_id', 'sentence_fragments.gloss_id')
                ->first();

            if ($fragmentData !== null) {
                $inflectionIds = GlossInflection::where('sentence_fragment_id', $fragmentData->sentence_fragment_id) //
                    ->pluck('inflection_id');
                $glossId = $fragmentData->gloss_id;
                $speechId = $fragmentData->speech_id;
            }

            if ($glossId === null) {
                $gloss = Gloss::active()
                    ->join('words', 'words.id', '=', 'glosses.word_id')
                    ->where('language_id', $languageId)
                    ->where('normalized_word', $f['normalized'])
                    ->orderBy('glosses.speech_id', 'desc')
                    ->select('glosses.id', 'glosses.speech_id')
                    ->first();
                
                if ($gloss !== null) {
                    $glossId = $gloss->id;
                    $speechId = $gloss->speech_id;
                }
            }

            if ($glossId !== null) {
                $suggestions[$f['original']] = [
                    'gloss_id' => $glossId,
                    'speech_id' => $speechId,
                    'inflection_ids' => $inflectionIds
                ];
            }
        }

        return $suggestions;
    }
}
