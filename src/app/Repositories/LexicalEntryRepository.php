<?php

namespace App\Repositories;

use App\Events\LexicalEntryCreated;
use App\Events\LexicalEntryDestroyed;
use App\Events\LexicalEntryEdited;
use App\Events\SenseEdited;
use App\Helpers\StringHelper;
use App\Interfaces\ISystemLanguageFactory;
use App\Models\{
    Gloss,
    LexicalEntry,
    LexicalEntryDetail,
    Keyword,
    Language,
    Sense,
    Word
};
use App\Models\Versioning\{
    LexicalEntryVersion,
    GlossVersion,
    LexicalEntryDetailVersion
};
use App\Repositories\Enumerations\LexicalEntryChange;
use App\Repositories\ValueObjects\LexicalEntryVersionsValue;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LexicalEntryRepository
{
    protected KeywordRepository $_keywordRepository;

    protected WordRepository $_wordRepository;

    protected AuthManager $_authManager;

    protected ?Language $_systemLanguage;

    public function __construct(KeywordRepository $keywordRepository, WordRepository $wordRepository, AuthManager $authManager,
        ISystemLanguageFactory $systemLanguageFactory)
    {
        $this->_keywordRepository = $keywordRepository;
        $this->_wordRepository = $wordRepository;
        $this->_authManager = $authManager;
        $this->_systemLanguage = $systemLanguageFactory->language();
    }

    /**
     * Obtains the senses for the specified array of glosses and uses these senses to, in turn, find *all* glosses
     * with the same senses. This is useful for the search index, which currently only indexes glosses.
     *
     * @param  array  $glossIds  list of glosses
     * @param  int  $languageId  optional language parameter
     * @param  bool  $includeOld  optional is_old filter (false filters them out)
     * @param  array  $filters  optional filters, refer to `createGlossQuery` for more information.
     * @return array
     */
    public function getLexicalEntriesByExpandingViaSense(array $lexicalEntryIds, $languageId = 0, $includeOld = true, $filters = [])
    {
        $senseIds = LexicalEntry::whereIn('id', $lexicalEntryIds) //
            ->pluck('sense_id');

        $maximumNumberOfResources = config('ed.gloss_repository_maximum_results');
        $lexicalEntries = self::createLexicalEntryQuery($languageId, $includeOld, function ($q) use ($senseIds, $filters) {
            $q = $q->whereIn('g.sense_id', $senseIds);

            if (is_array($filters)) {
                foreach ($filters as $column => $values) {
                    $q = $q->whereIn($column, $values);
                }
            }

            return $q;
        }) //
            ->limit($maximumNumberOfResources) //
            ->get() //
            ->toArray();

        return $lexicalEntries;
    }

    /**
     * Gets a list of lexical entries with the specified IDs.
     *
     * @param  array  $ids  array of integers
     * @return array
     */
    public function getLexicalEntries(array $ids)
    {
        $maximumNumberOfResources = config('ed.gloss_repository_maximum_results');

        return self::createLexicalEntryQuery(0, true /* = include old */, function ($q) use ($ids) {
            return $q->whereIn('g.id', $ids);
        })
            ->limit($maximumNumberOfResources)
            ->get()
            ->toArray();
    }

    /**
     * Gets a list of lexical entries within the specified lexical entry group that matches the specified external ID.
     *
     * @param  string  $externalId  external source ID
     * @return array
     */
    public function getLexicalEntriesByExternalId(string $externalId, int $lexicalEntryGroupId)
    {
        $maximumNumberOfResources = config('ed.gloss_repository_maximum_results');

        return self::createLexicalEntryQuery(0, true /* = include old */, function ($q) use ($externalId, $lexicalEntryGroupId) {
            $q = $q->where('g.external_id', $externalId);
            if ($lexicalEntryGroupId !== 0) {
                $q = $q->where('g.lexical_entry_group_id', $lexicalEntryGroupId);
            }

            return $q;
        })
            ->limit($maximumNumberOfResources)
            ->get()
            ->toArray();
    }

    /**
     * Gets the lexical entry entity with the specified ID.
     *
     * @return Collection
     */
    public function getLexicalEntry(int $id)
    {
        $lexicalEntry = LexicalEntry::where('id', $id)
            ->with('account', 'sense', 'speech', 'sense.word', 'lexical_entry_group', 'word', 'glosses', 'lexical_entry_details')
            ->first();

        if ($lexicalEntry === null) {
            return new Collection; // Empty collection, i.e. no lexical entry was found.
        }

        return new Collection([$lexicalEntry]);
    }

    /**
     * Gets the latest version of the lexical entry specified by the ID.
     *
     * @return LexicalEntryVersion
     */
    public function getLatestLexicalEntryVersion(int $lexicalEntryId)
    {
        $version = LexicalEntryVersion::where('lexical_entry_id', $lexicalEntryId)
            ->with('glosses', 'lexical_entry_details', 'word')
            ->orderBy('created_at', 'desc') // order by latest
            ->orderBy('id', 'desc')
            ->first();

        return $version;
    }

    /**
     * Gets all versions of the lexical entry specified by the ID.
     *
     * @return Collection
     */
    public function getLexicalEntryVersions(int $lexicalEntryId)
    {
        $versions = LexicalEntryVersion::where('lexical_entry_id', $lexicalEntryId)
            ->with('glosses', 'lexical_entry_details', 'word')
            ->orderBy('created_at', 'desc') // order by latest
            ->orderBy('id', 'desc')
            ->get();

        return new LexicalEntryVersionsValue([
            'versions' => $versions,
            'latest_version_id' => $versions->count() > 0 ? $versions->first()->id : null,
        ]);
    }

    public function getSpecificLexicalEntryVersion(int $versionId)
    {
        $version = LexicalEntryVersion::with('glosses', 'lexical_entry_details', 'word')
            ->find($versionId);

        return $version;
    }

    public function getLexicalEntryFromVersion(int $versionId)
    {
        $version = $this->getSpecificLexicalEntryVersion($versionId);
        if ($version === null) {
            return null;
        }

        $lexicalEntry = new LexicalEntry($version->getAttributes());

        if (! $lexicalEntry->sense_id) {
            $sense = LexicalEntry::where('id', $version->lexical_entry_id)
                ->select('sense_id')
                ->first();
            if ($sense === null) {
                return null;
            }

            $lexicalEntry->sense_id = $sense->sense_id;
        }

        $lexicalEntry->setAttribute('id', $version->lexical_entry_id);
        $lexicalEntry->exists = true;

        $lexicalEntry->load('account', 'lexical_entry_group', 'language', 'sense', 'sense.word', 'speech', 'word');
        $lexicalEntry->setAttribute('glosses', $version->glosses->map(function ($t) {
            return new Gloss($t->getAttributes());
        }));
        $lexicalEntry->setAttribute('lexical_entry_details', $version->lexical_entry_details->map(function ($d) {
            return new LexicalEntryDetail($d->getAttributes());
        }));

        return $lexicalEntry;
    }

    /**
     * Gets the latest lexical entry associated with the legacy gloss ID.
     *
     * @param  int  $glossId  legacy gloss ID (pre-migration)
     * @return LexicalEntryVersion
     */
    public function getLexicalEntryVersionByPreMigrationId(int $glossId)
    {
        Log::warning('[DEPRECATED] Calling getLexicalEntryVersionByPreMigrationId for '.$glossId);
        $version = LexicalEntryVersion::where('__migration_gloss_id', $glossId)->first();

        return $version;
    }

    public function suggest(array $words, int $languageId, $inexact = false)
    {
        // Transform all words to lower case and remove doublettes.
        $words = array_unique(array_map(function ($s) {
            return mb_strtolower($s, 'utf-8');
        }, $words));

        // Create an array containing words in their ASCII form. These
        // will be used to query the database with.
        $normalizedWords = array_unique(array_map(function ($s) use ($inexact) {
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

        $fields = [
            'w.normalized_word',
            'w.word',
            'g.comments',
            's.name as type',
            't.translation',
            'g.source',
            'a.nickname as account_name',
            'tg.name as gloss_group_name',
            'g.id',
        ];
        $query = self::createLexicalEntryQueryWithoutDetails($fields, false);

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
            ->where([
                ['g.is_deleted', 0],
            ])
            ->orderBy(DB::raw('CHAR_LENGTH(w.normalized_word)'))
            ->limit($numberOfNormalizedWords * 15)
            ->get();

        if ($suggestions->count() > 0) {
            foreach ($words as $word) {
                $lengthOfWord = strlen($word);

                // Try to find direct matches first, i.e. á => á.
                $matchingSuggestions = $suggestions->filter(function ($s) use ($word, $lengthOfWord) {
                    return strlen($s->word) >= $lengthOfWord && substr($word, 0, $lengthOfWord) === $word;
                });

                if ($matchingSuggestions->count() < 1) {
                    // If no direct matches were found, normalize the word and try again, i.e. a => a
                    $normalizedWord = StringHelper::normalize($word);
                    $lengthOfWord = strlen($normalizedWord);

                    $matchingSuggestions = $suggestions->filter(function ($s) use ($normalizedWord, $lengthOfWord) {
                        return strlen($s->normalized_word) >= $lengthOfWord &&
                            substr($s->normalized_word, 0, $lengthOfWord) === $normalizedWord;
                    });
                }

                $groupedSuggestions[$word] = $matchingSuggestions->values();
            }
        }

        return $groupedSuggestions;
    }

    public function saveLexicalEntry(string $wordString, string $senseString, LexicalEntry $lexicalEntry, array $glosses, array $keywords, array $details = [], int &$changed = 0)
    {
        if (! $lexicalEntry instanceof LexicalEntry) {
            throw new \Exception('LexicalEntry must be an instance of the LexicalEntry class.');
        }

        foreach ($glosses as $gloss) {
            if (! ($gloss instanceof Gloss)) {
                throw new \Exception('The array of glosses must consist of instances of the Gloss model.');
            }
        }

        foreach ($details as $detail) {
            if (! ($detail instanceof LexicalEntryDetail)) {
                throw new \Exception('The array of details must consist of instances of the LexicalEntryDetail model.');
            }
        }

        foreach ($keywords as $keyword) {
            if (! is_string($keyword)) {
                throw new \Exception(sprintf('The array of keywords must only contain strings. Found "%s".', json_encode($keyword)));
            }
        }

        if (! $lexicalEntry->account_id) {
            throw new \Exception('Invalid account ID.');
        }

        // All words should be lower case.
        $wordString = StringHelper::toLower($wordString);
        $senseString = StringHelper::toLower($senseString);

        // Retrieve existing or create a new word entity for the sense and the word.
        $word = $this->_wordRepository->save($wordString, $lexicalEntry->account_id);
        $senseWord = $this->_wordRepository->save($senseString, $lexicalEntry->account_id);

        // Load sense or create it if it doesn't exist. A sense is 1:1 mapped with
        // words, and therefore doesn't have its own incrementing identifier.
        $sense = $this->createSense($senseWord);

        // Generate a collection of keywords
        $glossStrings = array_map(function ($g) {
            return $g->gloss;
        }, $glosses);
        $newKeywords = array_unique(array_merge($keywords, [$wordString, $senseString], $glossStrings));
        sort($newKeywords);

        // These variables will be set to true if any changes are detected on the specified entity.
        $changed = LexicalEntryChange::NO_CHANGE->value;
        $isNew = false;

        // Load the original lexical entry and update the lexical entry's origin and parent columns.
        if (! $lexicalEntry->id && $lexicalEntry->external_id) {
            $existingLexicalEntry = LexicalEntry::where('external_id', $lexicalEntry->external_id)->first();

            if ($existingLexicalEntry) {
                // Cannot assign to $lexicalEntry->id directly due to property protection.
                // Instead, set the key via fill or merge, or handle accordingly.
                $lexicalEntry->setAttribute('id', $existingLexicalEntry->id);
                unset($existingLexicalEntry);
            }
        }

        if ($lexicalEntry->id) {
            $originalLexicalEntry = LexicalEntry::with('sense', 'glosses', 'word', 'keywords', 'lexical_entry_details')
                ->findOrFail($lexicalEntry->id);

            // Detect changes to the following aspects of the lexical entry:
            // - info: any metadata about the lexical entry
            // - glosses: English gloss collection
            // - details: optional details collection
            // - keywords: generated search keywords
            //
            // The repository will only update the aspects that has been
            // modified.
            if ($originalLexicalEntry->sense_id !== $sense->id ||
                $originalLexicalEntry->word_id !== $word->id) {
                $changed |= LexicalEntryChange::WORD_OR_SENSE->value;
            }

            if (! $originalLexicalEntry->equals($lexicalEntry)) {
                $changed |= LexicalEntryChange::METADATA->value;
            }

            // If no other parameters have changed, iterate through the list of glosses
            // and determine whether there are mismatching glosses.
            //
            // Begin by checking the length of the two collections. Laravel collections are not
            // used by the repository, but is utilised by the Eloquent ORM, thus the different
            // syntax.
            if ($originalLexicalEntry->glosses->count() !== count($glosses)) {
                $changed |= LexicalEntryChange::TRANSLATIONS->value;
            }

            if (! ($changed & LexicalEntryChange::TRANSLATIONS->value)) {
                // If the length matches, iterate through each element and check whether they
                // exist in both collections.
                $existingGlosses = $originalLexicalEntry->glosses->map(function ($g) {
                    return $g->gloss;
                });
                $newGlosses = collect($glosses)->map(function ($g) {
                    return $g->gloss;
                });

                $existingGlosses->sort();
                $newGlosses->sort();

                if ($newGlosses != $existingGlosses) {
                    $changed |= LexicalEntryChange::TRANSLATIONS->value;
                }
            }

            // Lexical entry details changed?
            if ($originalLexicalEntry->lexical_entry_details->count() !== count($details)) {
                $changed |= LexicalEntryChange::DETAILS->value;
            }

            if (! ($changed & LexicalEntryChange::DETAILS->value)) {
                foreach ($details as $d) {
                    if (! $originalLexicalEntry->lexical_entry_details->contains(function ($od) use ($d) {
                        return $od->category === $d->category &&
                                $od->order === $d->order &&
                                $od->text === $d->text;
                    })) {
                        $changed |= LexicalEntryChange::DETAILS->value;
                        break;
                    }
                }
            }

            $oldKeywords = array_unique(
                $originalLexicalEntry->keywords->map(function ($f) {
                    return $f->keyword;
                })->toArray()
            );
            sort($oldKeywords);

            if ($newKeywords != $oldKeywords) {
                $changed |= LexicalEntryChange::KEYWORDS->value;
            }
        } else {
            $changed |= LexicalEntryChange::NEW->value;
        }

        if ($changed !== LexicalEntryChange::NO_CHANGE->value) {
            try {
                DB::beginTransaction();

                $isNew = $changed & LexicalEntryChange::NEW->value;

                if ($isNew || $changed & LexicalEntryChange::METADATA->value) {
                    $lexicalEntry->word_id = $word->id;
                    $lexicalEntry->sense_id = $sense->id;
                    $lexicalEntry->is_deleted = 0;
                    $lexicalEntry->has_details = count($details) > 0;
                    $lexicalEntry->save();
                    $lexicalEntry->refresh();
                } elseif ($changed & LexicalEntryChange::WORD_OR_SENSE->value) {
                    $lexicalEntry->word_id = $word->id;
                    $lexicalEntry->sense_id = $sense->id;
                    $lexicalEntry->save();
                    $lexicalEntry->refresh();
                }

                if ($isNew || $changed & LexicalEntryChange::DETAILS->value) {
                    if (! $isNew) {
                        $lexicalEntry->lexical_entry_details()->delete();
                    }
                    $lexicalEntry->lexical_entry_details()->saveMany($details);
                }

                if ($isNew || $changed & LexicalEntryChange::TRANSLATIONS->value) {
                    if (! $isNew) {
                        $lexicalEntry->glosses()->delete();
                    }
                    $lexicalEntry->glosses()->saveMany($glosses);
                }

                if ($isNew || $changed & LexicalEntryChange::KEYWORDS->value) {
                    if (! $isNew) {
                        $lexicalEntry->keywords()->delete();
                    }

                    foreach ($newKeywords as $keyword) {
                        $keywordWord = $this->_wordRepository->save($keyword, $lexicalEntry->account_id);
                        $keywordLanguage = (
                            in_array($keyword, $glossStrings) || //
                            $senseString === $keyword
                        ) && $keyword !== $wordString //
                            ? $this->_systemLanguage : $lexicalEntry->language;
                        $this->_keywordRepository->createKeyword($keywordWord, $sense, $lexicalEntry, $keywordLanguage);
                    }
                }

                if ($changed !== LexicalEntryChange::NO_CHANGE->value) {
                    $this->saveVersion($lexicalEntry, $changed);
                }

                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }
        }

        // 13. Register an audit trail
        if ($changed !== LexicalEntryChange::NO_CHANGE->value) {
            $event = $isNew ? new LexicalEntryCreated($lexicalEntry, $lexicalEntry->account_id) //
                            : new LexicalEntryEdited($lexicalEntry, $this->_authManager->check() ? $this->_authManager->user()->id : $lexicalEntry->account_id);

            event($event);

            if ($isNew || $changed & LexicalEntryChange::KEYWORDS->value) {
                event(new SenseEdited($sense));
            }
        }

        return $lexicalEntry;
    }

    public function deleteLexicalEntryWithId(int $id, ?int $replaceId = null)
    {
        $lexicalEntry = LexicalEntry::findOrFail($id);

        // Deleted lexical entries or deprecated (replaced) lexical entries cannot be deleted.
        if ($lexicalEntry->is_deleted) {
            return false;
        }

        $this->deleteLexicalEntry($lexicalEntry, $replaceId);

        return true;
    }

    /**
     * Gets keywords for the specified lexical entry.
     *
     * @return \stdClass
     */
    public function getKeywords(int $senseId, int $id)
    {
        $keywords = Keyword::join('words', 'words.id', 'keywords.word_id')
            ->where('keywords.sense_id', $senseId)
            ->where(function ($query) use ($id) {
                $query->whereNull('keywords.gloss_id')
                    ->orWhere('keywords.gloss_id', $id);
            })
            ->select('words.word')
            ->distinct()
            ->orderBy('words.word')
            ->get();

        return $keywords;
    }

    protected function createSense(Word $senseWord): Sense
    {
        $sense = Sense::find($senseWord->id);

        if (! $sense) {
            $sense = new Sense;
            $sense->setAttribute('id', $senseWord->id);
            $sense->description = $senseWord->word;

            $sense->save();
        }

        return $sense;
    }

    /**
     * Creates a lexical entry query using the QueryBuilder API. The following aliases are specified:
     * - g:  lexical_entries
     * - w:  words
     * - t:  glosses
     * - a:  accounts
     * - tg: lexical_entry_groups
     * - s:  speeches
     *
     * The method returns a query builder object, with a SELECT instruction. You can optionally append
     * further filters, and simply _get()_ when ready.
     */
    protected static function createLexicalEntryQuery(int $languageId = 0, bool $includeOld = true, ?callable $whereCallback = null): Builder
    {
        $filters = [
            ['g.is_deleted', 0],
        ];

        if (! $includeOld) {
            $filters[] = ['tg.is_old', 0];
        }

        if ($languageId !== 0) {
            $filters[] = ['g.language_id', $languageId];
        }

        static $columns = [
            'w.word', 'g.id', 't.translation', 'g.etymology', 's.name as type', 'g.source',
            'g.comments', 'g.tengwar', 'g.language_id', 'g.account_id', 'a.nickname as account_name',
            'w.normalized_word', 'g.created_at', 'g.updated_at', 'g.lexical_entry_group_id', 'tg.name as lexical_entry_group_name',
            'tg.is_canon', 'tg.is_old', 'tg.external_link_format', 'g.is_uncertain', 'g.external_id', 'g.is_rejected',
            'g.sense_id', 'tg.label as lexical_entry_group_label', 'g.label', 'g.latest_lexical_entry_version_id',
        ];

        $q0 = self::createLexicalEntryQueryWithoutDetails($columns, true)
            ->where($filters);

        if ($whereCallback != null) {
            $tmp = $whereCallback($q0);
            if ($tmp) {
                $q0 = $tmp;
            }
        }

        return $q0;
    }

    protected static function createLexicalEntryQueryWithoutDetails(array $columns, bool $addDetailsColumns)
    {
        $query = DB::table('lexical_entries as g')
            ->join('glosses as t', 'g.id', 't.lexical_entry_id')
            ->join('accounts as a', 'g.account_id', 'a.id')
            ->join('words as w', 'g.word_id', 'w.id')
            ->leftJoin('lexical_entry_groups as tg', 'g.lexical_entry_group_id', 'tg.id')
            ->leftJoin('speeches as s', 'g.speech_id', 's.id');

        if ($addDetailsColumns) {
            $query = $query->leftJoin('gloss_details as gd', 'g.id', 'gd.gloss_id');
            $columns = array_merge($columns, [
                DB::raw('gd.category as gloss_details_category'),
                DB::raw('gd.text as gloss_details_text'),
                DB::raw('gd.category as gloss_details_order'),
                DB::raw('gd.type as gloss_details_type'),
            ]);
        }

        return $query->select($columns);
    }

    protected function deleteLexicalEntry(LexicalEntry $lexicalEntry, ?int $replaceId = null)
    {
        $replacement = $replaceId ? LexicalEntry::findOrFail($replaceId) : null;

        $lexicalEntry->keywords()->delete();

        // delete the sense if the specified lexical entry is the only lexical entry with that sense.
        if ($lexicalEntry->sense->lexical_entries()->count() === 1 /* = $lexicalEntry */) {
            $lexicalEntry->sense->keywords()->delete();
        }

        if (! $replaceId !== null) {
            $lexicalEntry->sentence_fragments()->update([
                'lexical_entry_id' => $replaceId,
            ]);

            $lexicalEntry->contributions()->update([
                'lexical_entry_id' => null,
            ]);
        }

        $lexicalEntry->is_deleted = true;
        $lexicalEntry->save();

        event(new LexicalEntryDestroyed($lexicalEntry, $replacement, $this->_authManager->check() ? $this->_authManager->user()->id : 0));
    }

    protected function saveVersion(LexicalEntry $lexicalEntry, int $changes)
    {
        // Make sure we're working with the latest version of the lexical entry.
        $lexicalEntry->refresh();

        $data = $lexicalEntry->toArray();
        $data['lexical_entry_id'] = $data['id'];
        $data['version_change_flags'] = $changes;
        unset($data['id']);

        try {
            DB::beginTransaction();

            $version = LexicalEntryVersion::create($data);
            $version->lexical_entry_details()->saveMany(
                $lexicalEntry->lexical_entry_details->map(function ($d) {
                    return new LexicalEntryDetailVersion($d->getAttributes());
                })
            );
            $version->glosses()->saveMany(
                $lexicalEntry->glosses->map(function ($t) {
                    return new GlossVersion($t->getAttributes());
                })
            );

            $lexicalEntry->latest_lexical_entry_version_id = $version->id;
            $lexicalEntry->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }
}
