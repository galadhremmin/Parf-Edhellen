<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Auth;

use App\Helpers\StringHelper;
use App\Events\{
    GlossCreated,
    GlossEdited
};
use App\Models\{ 
    Keyword,
    Gloss, 
    Translation,
    Sense, 
    Word 
};

class GlossRepository
{
    protected $_keywordRepository;

    public function __construct(KeywordRepository $keywordRepository)
    {
        $this->_keywordRepository = $keywordRepository;
    }

    public function getKeywordsForLanguage(string $word, $reversed = false, $languageId = 0, $includeOld = true, array $speechIds = null,
        array $glossGroupIds = null)
    {
        $hasWildcard = null;
        $word = self::formatWord($word, $hasWildcard);

        // TODO: #27 Completely refactor this logic. This search method is ridiculously poorly implemented.
        if ($languageId !== 0 || ! empty($speechIds) || ! empty($glossGroupIds)) {
            $filter = [
                [ 'g.is_latest', 1 ],
                [ 'g.is_deleted', 0 ],
                [ $reversed ? 'k.reversed_normalized_keyword_unaccented' : 'k.normalized_keyword_unaccented', 'like', $word ]
            ];
    
            if ($languageId > 0) {
                $filter[] = [ 'g.language_id', $languageId ];
            }

            if ($hasWildcard) {
                $filter[] = [ 'k.word_id', '=', DB::raw('g.word_id') ];
            }

            if (! $includeOld) {
                $filter[] = [ 'k.is_old', 0 ];
            }

            $query = DB::table('keywords as k')
                ->join('glosses as g', 'k.gloss_id', 'g.id')
                ->whereNotNull('k.gloss_id')
                ->where($filter);
    
            if (! empty($speechIds)) {
                $query = $query->whereIn('g.speech_id', $speechIds);
            }
    
            if (! empty($glossGroupIds)) {
                $query = $query->whereIn('g.gloss_group_id', $glossGroupIds);
            }
        } else {
            $query = Keyword::findByWord($word, $reversed, $includeOld);
        }

        $keywords = $query
            ->select('keyword as k', 'normalized_keyword as nk', 'reversed_normalized_keyword_unaccented_length as nrkul',
                'normalized_keyword_unaccented_length as nkul', 'reversed_normalized_keyword as rnk', 'word as ok')
            ->orderBy($reversed ? 'nrkul' : 'nkul', 'asc')
            ->orderBy($reversed ? 'rnk' : 'nk', 'asc')
            ->limit(100)
            ->distinct()
            ->get();

        return $keywords;
    }

    /**
     * Returns a list of glosses which match the specified word. This method looks for sense.
     *
     * @param string $word
     * @param int $languageId
     * @param bool $includeOld
     * @return array
     */
    public function getWordGlosses(string $word, int $languageId = 0, bool $includeOld = true, array $speechIds = null,
        array $glossGroupIds = null) 
    {
        if (empty($word)) {
            return [];
        }

        $senses = self::getSensesForWord($word);
        $collections = [
            'g.sense_id'       => $senses,
            'g.speech_id'      => $speechIds,
            'g.gloss_group_id' => $glossGroupIds
        ];
        $maximumNumberOfResources = config('ed.gloss_repository_maximum_results');

        return self::createGlossQuery($languageId, true /* = latest */, $includeOld, function($q) use($collections) {
                foreach ($collections as $column => $collection) {
                    if (is_array($collection) && count($collection) > 0) {
                        $q = $q->whereIn($column, $collection);
                    }
                }
                return $q;
            })
            ->limit($maximumNumberOfResources)
            ->get()
            ->toArray();
    }

    /**
     * Gets the gloss entity with the specified ID.
     * 
     * @param int $id
     * @return Collection
     */
    public function getGloss(int $id)
    {
        $gloss = Gloss::where('id', $id)
            ->with('account', 'sense', 'speech', 'sense.word', 'gloss_group', 'word', 'translations', 'gloss_details')
            ->first();

        if ($gloss === null) {
            return new Collection(); // Emtpy collection, i.e. no gloss was found.
        }

        return new Collection([ $gloss ]);
    }

    /**
     * Gets the version of the gloss specified by the ID.
     *
     * @param int $id
     * @return Collection
     */
    public function getGlossVersion(int $id) 
    {
        $gloss = self::createGlossQuery(0, false, true, function ($q) use($id) {
                return $q->where('g.id', $id);
            })
            ->get();

        return $gloss;
    }

    /**
     * Gets the version of the glosses specified by the IDs.
     *
     * @param int $id
     * @return Collection
     */
    public function getGlossVersions(array $ids) 
    {
        $glosses = self::createGlossQuery(0, false, true, function ($q) use($ids) {
                return $q->whereIn('g.id', $ids);
            })
            ->get();

        return $glosses;
    }

    /**
     * Gets the ID for the latest entity associated with the specified origin.
     *
     * @param int $originGlossId
     * @return int
     */
    public function getLatestGloss(int $originGlossId)
    {
        $data = DB::table('glosses')
            ->where([
                ['glosses.origin_gloss_id', $originGlossId],
                ['glosses.is_latest', 1]
            ])
            ->select('id')
            ->first();

        return $data->id;
    }

    public function getVersions(int $id)
    {
        $gloss = Gloss::where('id', $id)
            ->select('id', 'origin_gloss_id')
            ->first();

        if ($gloss === null) {
            return [];  
        }

        $originId = $gloss->origin_gloss_id ?: $gloss->id;
        return self::createGlossQuery(0, false, true, function ($q) use ($originId) {
                return $q->where(function ($query) use($originId) {
                    $query->where('g.id', $originId)
                        ->orWhere('g.origin_gloss_id', $originId);
                });
            })
            ->get()
            ->toArray();
    }

    public function suggest(array $words, int $languageId, $inexact = false) 
    {
        // Transform all words to lower case and remove doublettes.
        $words = array_unique(array_map(function ($s) {
            return mb_strtolower($s, 'utf-8');
        }, $words));

        // Create an array containing words in their ASCII form. These
        // will be used to query the database with.
        $normalizedWords = array_unique(array_map(function ($s) use($inexact) {
            $w = StringHelper::normalize($s);
            if ($inexact) {
                $w .= '%';
            }
            return $w;
        }, $words));

        // Initialize the grouped suggestions hash array with an empty array
        // per word. An empty array will tell the caller that no suggestions
        // were found.
        $groupedSuggestions = [];
        foreach ($words as $word) {
            $groupedSuggestions[$word] = [];
        }

        $numberOfNormalizedWords = count($normalizedWords);
        if ($numberOfNormalizedWords < 1) {
            return $groupedSuggestions;
        }

        $query = self::createGlossQueryWithoutDetails([
            'w.normalized_word',
            'w.word',
            'g.comments',
            's.name as type',
            't.translation',
            'g.source',
            'a.nickname as account_name',
            'tg.name as gloss_group_name',
            'g.id'
        ], false);

        if ($languageId !== 0) {
            $query = $query->where('g.language_id', $languageId);
        }
        
        if ($inexact) {
            $query->where(function ($query) use ($normalizedWords) {
                foreach ($normalizedWords as $normalizedWord) {
                    $query->orWhere('w.normalized_word', 'like', $normalizedWord);
                }
            });
        } else {
            $query = $query->whereIn('w.normalized_word', $normalizedWords);
        }

        $suggestions = $query
            ->orderBy(DB::raw('CHAR_LENGTH(w.normalized_word)'))
            ->limit($numberOfNormalizedWords*15)
            ->get()
            ->toArray();
        
        if (count($suggestions) > 0) {
            foreach ($words as $word) {
                $lengthOfWord = strlen($word);
                
                // Try to find direct matches first, i.e. á => á.
                $matchingSuggestions = array_filter($suggestions, function($s) use($word, $lengthOfWord) {
                    return strlen($s->word) >= $lengthOfWord && substr($word, 0, $lengthOfWord) === $word;
                });

                if (count($matchingSuggestions) < 1) {
                    // If no direct matches were found, normalize the word and try again, i.e. a => a
                    $normalizedWord = StringHelper::normalize($word);
                    $lengthOfWord = strlen($normalizedWord);

                    $matchingSuggestions = array_filter($suggestions, function ($s) use ($normalizedWord, $lengthOfWord) {
                        return strlen($s->normalized_word) >= $lengthOfWord && 
                            substr($s->normalized_word, 0, $lengthOfWord) === $normalizedWord;
                    });
                }

                $groupedSuggestions[$word] = $matchingSuggestions;
            }
        }

        return $groupedSuggestions;
    }

    public function saveGloss(string $wordString, string $senseString, Gloss $gloss, array $translations, array $keywords, array $details = [], $resetKeywords = true, bool & $changed = null)
    {
        if (! $gloss instanceof Gloss) {
            throw new \Exception("Gloss must be an instance of the Gloss class.");
        }

        foreach ($translations as $translation) {
            if (! ($translation instanceof Translation)) {
                throw new \Exception('The array of translations must consist of instances of the Translation model.');
            }
        }

        foreach ($keywords as $keyword) {
            if (! is_string($keyword)) {
                throw new \Exception(sprintf('The array of keywords must only contain strings. Found "%s".', json_encode($keyword)));
            }
        }

        if (! $gloss->account_id) {
            throw new \Exception('Invalid account ID.');
        }

        foreach ($details as $detail) {
            if (! $detail->account_id) {
                throw new \Exception('Invalid account ID for details.');
            }
        }

        // 1. Turn all words should be lower case.
        $wordString   = StringHelper::toLower($wordString);
        $senseString  = StringHelper::toLower($senseString);

        // 2. Retrieve existing or create a new word entity for the sense and the word.
        $word          = $this->createWord($wordString, $gloss->account_id);
        $senseWord     = $this->createWord($senseString, $gloss->account_id);

        // 3. Load sense or create it if it doesn't exist. A sense is 1:1 mapped with
        // words, and therefore doesn't have its own incrementing identifier.
        $sense = $this->createSense($senseWord);

        // 4. Load the original translation and update the translation's origin and parent columns.
        $changed = true;
        $translationsChanged = true;
        $originalGloss = null;

        if (! $gloss->id && $gloss->external_id) {
            $existingGloss = Gloss::where('external_id', $gloss->external_id)
                ->first();
            
            if ($existingGloss) {
                $existingGloss = $existingGloss->getLatestVersion();
                $gloss->id = $existingGloss->id;
                unset($existingGloss);
            }
        }

        if ($gloss->id) {
            $originalGloss = Gloss::with('sense', 'translations', 'word', 'keywords', 'gloss_details')
                ->findOrFail($gloss->id)->getLatestVersion();

            // 5. were there changes made?
            $changed = $originalGloss->sense_id !== $sense->id ||
                       $originalGloss->word_id !== $word->id;
            
            if (! $changed) {
                $newAttributes = $gloss->attributesToArray();
                $oldAttributes = $originalGloss->attributesToArray();

                foreach ($newAttributes as $key => $value) {
                    // Skip dates, as they are bound to be different.
                    if ($key === 'created_at' || $key === 'updated_at') {
                        continue;
                    }

                    // avoid perfect equality (===/!==) because the value in the DB
                    // can diverge from the one passed from the view.
                    if ($oldAttributes[$key] != $value) {
                        $changed = true;
                        break;
                    }
                }
            }
            
            // If no other parameters have changed, iterate through the list of translations 
            // and determine whether there are mismatching translations.
            //
            // Begin by checking the length of the two collections. Laravel collections are not
            // used by the repository, but is utilised by the Eloquent ORM, thus the different
            // syntax.
            $translationsChanged = $originalGloss->translations->count() !== count($translations);

            if (! $translationsChanged) {
                // If the length matches, iterate through each element and check whether they 
                // exist in both collections.
                foreach ($translations as $t) {
                    if (! $originalGloss->translations->contains(function ($ot) use($t) {
                        return $ot->translation === $t->translation;
                    })) {
                        // When not existing, the collection has changed.
                        $translationsChanged = true;
                        break;
                    }
                }
            }
            
            if ($translationsChanged) {
                $changed = true;
            }

            // Gloss details changed?
            if (! $changed) {
                $detailsChanged = $originalGloss->gloss_details->count() !== count($details);
                if (! $detailsChanged) {
                    foreach ($details as $d) {
                        if (! $originalGloss->gloss_details->contains(function ($od) use($d) {
                            return $od->category === $d->category &&
                                   $od->order === $d->order &&
                                   $od->text === $d->text;
                        })) {
                            $detailsChanged = true;
                            break;
                        }
                    }
                }

                if ($detailsChanged) {
                    $changed = true;
                }
            }

            if ($changed) {
                $gloss = $gloss->replicate();
                $gloss->origin_gloss_id = $originalGloss->origin_gloss_id ?: $originalGloss->id;
                $gloss->child_gloss_id = null;

                // 6. If the sense has changed, check whether the previous sense should be excluded from
                // the keywords table, which should only contain keywords to current senses.
                if ($originalGloss->sense_id !== $sense->id) {
                    $originalSense = Sense::find($originalGloss->sense_id);

                    // is the original gloss the only one associated with this sense?
                    if ($originalSense !== null && $originalSense->glosses()->count() === 1) {
                        // delete the sense's keywords as the sense is no longer in use.
                        $originalSense->keywords()->delete();
                    }
                }
            }
        } else {
            $gloss->origin_gloss_id = null;
        }

        // 7. Save changes as a _new_ row.
        if ($changed) {
            $gloss->word_id  = $word->id;
            $gloss->sense_id = $sense->id;
            $gloss->is_latest = 1;
            $gloss->is_deleted = 0;
            $gloss->is_index = 0;
            $gloss->has_details = count($details) > 0;
            $gloss->save();
            $gloss->translations()->saveMany($translations);
            $gloss->gloss_details()->saveMany($details);

            // 8. Update existing associations to the new entity.
            if ($originalGloss !== null) {
                $originalGloss->child_gloss_id = $gloss->id;
                $originalGloss->is_latest = 0;
                $originalGloss->save();

                $originalGloss->sentence_fragments()->update([
                    'gloss_id' => $gloss->id
                ]);
                $originalGloss->contributions()->update([
                    'gloss_id' => $gloss->id
                ]);
                $originalGloss->favourites()->update([
                    'gloss_id' => $gloss->id
                ]);
            }
        }
        
        // 9. Process keywords -- filter through the keywords and remove keywords that
        // match the gloss and the translation's word, as these are managed separately.
        $translationStrings = array_map(function ($t) {
            return $t->translation;
        }, $translations);
        $keywords = array_filter($keywords, function ($w) use($wordString, $translationStrings) {
            return $w !== $wordString && ! in_array($w, $translationStrings);
        });
        
        // 10. Remove existing keywords, if they have changed
        $keywordsChanged = $translationsChanged || $changed;
        if ($originalGloss !== null && ! $keywordsChanged) {
            // transform original keyword entities to an array of strings.
            $originalKeywords = array_merge(
                $originalGloss->keywords->map(function ($k) {
                        return $k->keyword;
                    })->toArray(),

                $originalGloss->sense->keywords()
                    ->whereNull('gloss_id')
                    ->get()
                    ->map(function ($k) {
                        return $k->keyword;
                    })->toArray()
            );

            // Create an array of keywords for the original entity as well as the new entity, and sort them. 
            // Once sorted, simple equality check can be carried out to determine whether the arrays are identical.
            $originalKeywords = array_unique($originalKeywords);
            $newKeywords = array_merge($keywords, [ $wordString ], $translationStrings);

            sort($originalKeywords);
            sort($newKeywords);
            
            $keywordsChanged = $originalKeywords !== $newKeywords;
        }

        // 11. save gloss and word as keywords on the translation, if changed.
        if ($keywordsChanged) {
            if ($originalGloss) {
                $originalGloss->keywords()->delete();
            }

            $this->_keywordRepository->createKeyword($word, $sense, $gloss);

            foreach ($translationStrings as $translationString) {
                $translationWord = $this->createWord($translationString, $gloss->account_id);

                if ($translationWord->id !== $gloss->word_id) {
                    $this->_keywordRepository->createKeyword($translationWord, $sense, $gloss);
                }
            }

            // 12. Register keywords on the sense
            // 12a. Delete existing keywords associated with the sense.
            if ($resetKeywords) {
                $sense->keywords()->whereNull('gloss_id')->delete();
            }

            // 12b. Recreate the keywords for the sense.
            foreach ($keywords as $keyword) {
                $keywordWord = $this->createWord($keyword, $gloss->account_id);

                if ($sense->keywords()->where('word_id', $keywordWord->id)->count() < 1) {
                    $this->_keywordRepository->createKeyword($keywordWord, $sense, null);
                }
            }
        }

        // 13. Register an audit trail
        if ($changed || $keywordsChanged || $originalGloss === null) {

            $event = ($originalGloss === null)
                ? new GlossCreated($gloss, $gloss->account_id) 
                : new GlossEdited($gloss, Auth::check() ? Auth::user()->id : $gloss->account_id);
            
            event($event);
        }

        return $gloss;
    }

    public function deleteGlossWithId(int $id, int $replaceId = null)
    {
        $gloss = Gloss::findOrFail($id);

        // Deleted glosses or deprecated (replaced) glosses cannot be deleted.
        if ($gloss->is_deleted || ! $gloss->is_latest) {
            return false;
        }

        // Only indexes can be permanently deleted (DELETE).
        $permanentDeletion = $gloss->is_index;

        if ($permanentDeletion) {
            // Delete all indexes in their entirety.
            $t = $gloss->getOrigin();
            while ($t) {
                $child = $t->getChild();
                $this->deleteGloss($t);
                $t = $child;
            }
        } else {
            $this->deleteGloss($gloss, $replaceId);
        }

        return true;
    }

    /**
     * Gets keywords for the specified gloss.
     *
     * @param int $senseId
     * @param int $id
     * @return \stdClass
     */
    public function getKeywords(int $senseId, int $id)
    {
        $keywords = Keyword::join('words', 'words.id', 'keywords.word_id')
                ->where('keywords.sense_id', $senseId)
                ->where(function ($query) use($id) {
                    $query->whereNull('keywords.gloss_id')
                        ->orWhere('keywords.gloss_id', $id);
                })
                ->select('words.word')
                ->distinct()
                ->orderBy('words.word')
                ->get();

        return $keywords;
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
            $ids[] = $row->sense_id;
        
        return $ids;
    }

    protected function createWord(string $wordString, int $accountId)
    {
        $wordString = mb_strtolower(trim($wordString), 'utf-8');
        $word = Word::whereRaw('BINARY word = ?', [ $wordString ])->first(); 

        if (! $word) {
            $normalizedWordString = StringHelper::normalize($wordString);
            
            $word = new Word;
            $word->word                     = $wordString;
            $word->normalized_word          = $normalizedWordString;
            $word->reversed_normalized_word = strrev($normalizedWordString); 
            $word->account_id               = $accountId;

            $word->save();
        }

        return $word; 
    }

    protected function createSense(Word $senseWord)
    {
        $sense = Sense::find($senseWord->id);
        
        if (! $sense) {
            $sense = new Sense;
            $sense->id = $senseWord->id;
            $sense->description = $senseWord->word;

            $sense->save();
        }

        return $sense;
    }

    /**
     * Creates a gloss query using the QueryBuilder API. The following aliases are specified:
     * - g:  glosses
     * - w:  words
     * - t:  translations
     * - a:  accounts
     * - tg: gloss_groups
     * - s:  speeches
     * 
     * The method returns a query builder object, with a SELECT instruction. You can optionally append
     * further filters, and simply _get()_ when ready.
     *
     * @param integer $languageId
     * @param boolean $latest
     * @param boolean $includeOld
     * @return Illuminate\Database\Eloquent\Builder
     */
    protected static function createGlossQuery($languageId = 0, $latest = true, $includeOld = true, callable $whereCallback = null) 
    {
        $filters = [
            ['g.is_deleted', 0],
            ['g.is_index', 0]
        ];

        if ($latest) {
            $filters[] = ['g.is_latest', 1];
        }

        if (! $includeOld) {
            $filters[] = ['tg.is_old', 0];
        }

        if ($languageId !== 0) {
            $filters[] = ['g.language_id', $languageId];
        }

        static $columns = [
            'w.word', 'g.id', 't.translation', 'g.etymology', 's.name as type', 'g.source',
            'g.comments', 'g.tengwar', 'g.phonetic', 'g.language_id', 'g.account_id',
            'a.nickname as account_name', 'w.normalized_word', 'g.is_index', 'g.created_at', 'g.gloss_group_id',
            'tg.name as gloss_group_name', 'tg.is_canon', 'tg.external_link_format', 'g.is_uncertain',
            'g.external_id', 'g.is_latest', 'g.is_rejected', 'g.origin_gloss_id', 'g.sense_id',
            'tg.label as gloss_group_label'
        ];

        $q0 = self::createGlossQueryWithoutDetails($columns, true)
            ->where('has_details', 0)
            ->where($filters);
        
        if ($whereCallback != null) {
            $tmp = $whereCallback($q0);
            if ($tmp) {
                $q0 = $tmp;
            }
        }

        $q1 = self::createGlossQueryWithDetails($columns)
            ->where($filters);
        
        if ($whereCallback != null) {
            $tmp = $whereCallback($q1);
            if ($tmp) {
                $q1 = $tmp;
            }
        }

        return $q1->union($q0);
    }

    protected static function createGlossQueryWithoutDetails(array $columns, bool $addDetailsColumns)
    {
        if ($addDetailsColumns) {
            $columns = array_merge($columns, [
                DB::raw('NULL as gloss_details_category'), 
                DB::raw('NULL as gloss_details_text'), 
                DB::raw('NULL as gloss_details_order')
            ]);
        }

        return DB::table('glosses as g')
            ->join('words as w', 'g.word_id', 'w.id')
            ->join('translations as t', 'g.id', 't.gloss_id')
            ->join('accounts as a', 'g.account_id', 'a.id')
            ->leftJoin('gloss_groups as tg', 'g.gloss_group_id', 'tg.id')
            ->leftJoin('speeches as s', 'g.speech_id', 's.id')
            ->select($columns);
    }

    protected static function createGlossQueryWithDetails(array $columns)
    {
        $columns = array_merge($columns, [
            'gd.category as gloss_details_category', 
            'gd.text as gloss_details_text', 
            'gd.order as gloss_details_order'
        ]);
        
        return self::createGlossQueryWithoutDetails($columns, false)
            ->join('gloss_details as gd', 'g.id', 'gd.gloss_id');
    }

    protected function deleteGloss(Gloss $g, int $replaceId = null) 
    {
        $g->keywords()->delete();
        
        // delete the sense if the specified gloss is the only gloss with that sense.
        if ($g->sense->glosses()->count() === 1 /* = $g */) {
            $g->sense->keywords()->delete();
        }

        if (! $g->is_index) {
            $g->sentence_fragments()->update([
                'gloss_id' => $replaceId
            ]);
            
            $g->favourites()->update([
                'gloss_id' => $replaceId
            ]);

            $g->contributions()->update([
                'gloss_id' => null
            ]);
        }

        $g->is_deleted = true;
        $g->save();
    }

    protected static function formatWord(string $word, bool& $hasWildcard = null) 
    {
        if (strpos($word, '*') !== false) {
            $hasWildcard = true;
            return str_replace('*', '%', $word);
        } 
        
        $hasWildcard = false;
        return $word.'%';
    }
}
