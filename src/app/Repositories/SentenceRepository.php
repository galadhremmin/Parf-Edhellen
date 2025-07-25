<?php

namespace App\Repositories;

use App\Events\SentenceCreated;
use App\Events\SentenceDestroyed;
use App\Events\SentenceEdited;
use App\Events\SentenceFragmentsDestroyed;
use App\Helpers\SentenceHelper;
use App\Helpers\StringHelper;
use App\Models\LexicalEntry;
use App\Models\Inflection;
use App\Models\Initialization\Morphs;
use App\Models\Sentence;
use App\Models\SentenceFragment;
use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SentenceRepository
{
    private LexicalEntryInflectionRepository $_lexicalEntryInflectionRepository;

    private SearchIndexRepository $_searchRepository;

    private AuthManager $_authManager;

    public function __construct(
        LexicalEntryInflectionRepository $lexicalEntryInflectionRepository,
        SearchIndexRepository $searchRepository,
        AuthManager $authManager)
    {
        $this->_lexicalEntryInflectionRepository = $lexicalEntryInflectionRepository;
        $this->_searchRepository = $searchRepository;
        $this->_authManager = $authManager;
    }

    /**
     * Gets the languages for all available sentences.
     *
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
     *
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
     * @param  number[]  $ids
     * @return array
     */
    public function getInflectionsForGlosses(array $ids)
    {
        return DB::table('sentence_fragments as sf')
            ->join('sentences as s', 'sf.sentence_id', 's.id')
            ->join('lexical_entry_inflections as gi', 'sf.id', 'gi.sentence_fragment_id')
            ->join('speeches as sp', 'gi.speech_id', 'sp.id')
            ->join('languages as l', 'gi.language_id', 'l.id')
            ->join('inflections as i', 'gi.inflection_id', 'i.id')
            ->whereIn('sf.lexical_entry_id', $ids)
            ->select('sf.lexical_entry_id', 'sf.fragment as word', 'i.name as inflection', 'sp.name as speech',
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

        $fragments = $sentence->sentence_fragments()->with(['lexical_entry_inflections', 'speech'])->get();
        $translations = $sentence->sentence_translations()
            ->select('sentence_number', 'paragraph_number', 'translation')
            ->orderBy('paragraph_number', 'asc')
            ->orderBy('sentence_number', 'asc')
            ->get();

        $sentence->makeHidden(['account_id', 'language_id', 'sentence_translations', 'sentence_fragments']);

        return [
            'sentence' => $sentence,
            'sentence_fragments' => $fragments,
            'sentence_translations' => $translations,
            'sentence_transformations' => resolve(SentenceHelper::class)->buildSentences($fragments),
            'speeches' => $this->getSpeechesForSentenceFragments($fragments),
            'inflections' => $this->getInflectionsForSentenceFragments($fragments),
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
            return $f->lexical_entry_inflections->map(function ($i) {
                return $i->inflection_id;
            });
        })->flatten()) //
            ->get() //
            ->keyBy('id');

        return $inflections;
    }

    public function saveSentence(Sentence $sentence, array $fragments, array $inflectionsPerFragments, array $translations = [])
    {
        $changed = (bool) $sentence->id;
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
                    $inflection->speech_id = $fragment->speech_id;
                    $inflection->lexical_entry_id = $fragment->lexical_entry_id;
                    $inflection->language_id = $sentence->language_id;
                    $inflection->account_id = $sentence->account_id;
                    $inflection->sentence_id = $fragment->sentence_id;
                    $inflection->sentence_fragment_id = $fragment->id;
                    $inflection->word = $fragment->fragment;
                }

                $this->_lexicalEntryInflectionRepository->saveInflectionAsOneGroup(collect($inflections));
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
            $fragment->lexical_entry_inflections()->delete();
            $fragment->keywords()->delete();
        }
        $sentence->sentence_fragments()->delete();

        event(new SentenceFragmentsDestroyed($fragments));
    }

    public function destroy(Sentence $sentence)
    {
        $this->destroyFragments($sentence);
        $sentence->sentence_translations()->delete();
        $sentence->delete();

        event(new SentenceDestroyed($sentence, $this->_authManager->user()->id));
    }

    public function suggestFragmentGlosses(Collection $fragments, int $languageId)
    {
        $distinctFragments = $fragments->filter(function ($f) {
            return $f->type === 0 && $f->lexical_entry_id === 0; // = i.e. words
        })->map(function ($f) {
            return StringHelper::toLower(StringHelper::clean($f->fragment));
        })->unique();

        $maximumFragments = config('ed.sentence_repository_maximum_fragments');
        if ($distinctFragments->count() > $maximumFragments) {
            $distinctFragments->splice(0, $maximumFragments);
        }

        // This can be a very costly query to run depending on the number of fragments
        // the phrase consists of. To optimize for database performance and to reduce
        // latency, we'll turn to the search index table for the suggestions. This table
        // is already optimized for queries like these.
        //
        // We need to narrow the search to the glossary and existing phrases. The index
        // groups their entries in so-called 'search groups'. Generally, the search group
        // is tied to the underlying entry's entity, so we can obtain the search group
        // IDs for the glossary and for fragments by aquiring the morph for for `LexicalEntry`
        // and `SentenceFragment` entities:
        $sentenceFragmentMorph = Morphs::getAlias(SentenceFragment::class);
        $lexicalEntryMorph = Morphs::getAlias(LexicalEntry::class);

        // Query the index but narrow the search to the language and the search groups
        // identified above.
        $results = $this->_searchRepository->indexSearch(
            $distinctFragments->all(),
            function ($query) use ($languageId, $sentenceFragmentMorph, $lexicalEntryMorph) {
                $query = $query
                    ->where('language_id', $languageId)
                    ->whereIn('entity_name', [
                        $sentenceFragmentMorph,
                        $lexicalEntryMorph,
                    ]);

                return $query;
            });

        // Results are grouped by the term. The term isn't normalized. This maps well with'
        // the associative array type we'd like to return from this method. Iterate through
        // the matching search index entries and pick the first most appropriate entry.
        $suggestions = [];
        foreach ($distinctFragments as $fragment) {
            if (! $results->has($fragment)) {
                continue;
            }

            foreach ($results[$fragment] as $result) {
                if ($result->entity_name === $sentenceFragmentMorph) {
                    // Protect against dangling/incorrect database entries. These can exist
                    // for legacy reasons.
                    if ($result->entity === null) {
                        continue;
                    }

                    $lexicalEntryId = $result->entity->lexical_entry_id;
                    $speechId = $result->entity->speech_id;
                    $inflectionIds = $result->entity->lexical_entry_inflections->pluck('inflection_id');
                } else {
                    $lexicalEntryId = $result->entity_id;
                    $speechId = $result->speech_id;
                    $inflectionIds = [];
                }

                $suggestions[$fragment] = [
                    'lexical_entry_id' => $lexicalEntryId,
                    'speech_id' => $speechId,
                    'inflection_ids' => $inflectionIds,
                ];

                break;
            }
        }

        return $suggestions;
    }
}
