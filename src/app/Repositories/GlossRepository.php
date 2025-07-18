<?php

namespace App\Repositories;

use App\Events\GlossCreated;
use App\Events\GlossDestroyed;
use App\Events\GlossEdited;
use App\Events\SenseEdited;
use App\Helpers\StringHelper;
use App\Interfaces\ISystemLanguageFactory;
use App\Models\Gloss;
use App\Models\GlossDetail;
use App\Models\Keyword;
use App\Models\Language;
use App\Models\Sense;
use App\Models\Translation;
use App\Models\Versioning\GlossDetailVersion;
use App\Models\Versioning\GlossVersion;
use App\Models\Versioning\TranslationVersion;
use App\Models\Word;
use App\Repositories\Enumerations\GlossChange;
use App\Repositories\ValueObjects\GlossVersionsValue;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlossRepository
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
    public function getGlossesByExpandingViaSense(array $glossIds, $languageId = 0, $includeOld = true, $filters = [])
    {
        $senseIds = Gloss::whereIn('id', $glossIds) //
            ->pluck('sense_id');

        $maximumNumberOfResources = config('ed.gloss_repository_maximum_results');
        $glosses = self::createGlossQuery($languageId, $includeOld, function ($q) use ($senseIds, $filters) {
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

        return $glosses;
    }

    /**
     * Gets a list of glosses with the specified IDs.
     *
     * @param  array  $ids  array of integers
     * @return array
     */
    public function getGlosses(array $ids)
    {
        $maximumNumberOfResources = config('ed.gloss_repository_maximum_results');

        return self::createGlossQuery(0, true /* = include old */, function ($q) use ($ids) {
            return $q->whereIn('g.id', $ids);
        })
            ->limit($maximumNumberOfResources)
            ->get()
            ->toArray();
    }

    /**
     * Gets a list of glosses within the specified gloss group that matches the specified external ID.
     *
     * @param  string  $externalId  external source ID
     * @return array
     */
    public function getGlossesByExternalId(string $externalId, int $glossGroupId)
    {
        $maximumNumberOfResources = config('ed.gloss_repository_maximum_results');

        return self::createGlossQuery(0, true /* = include old */, function ($q) use ($externalId, $glossGroupId) {
            $q = $q->where('g.external_id', $externalId);
            if ($glossGroupId !== 0) {
                $q = $q->where('g.gloss_group_id', $glossGroupId);
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
     * @return Collection
     */
    public function getGloss(int $id)
    {
        $gloss = Gloss::where('id', $id)
            ->with('account', 'sense', 'speech', 'sense.word', 'gloss_group', 'word', 'translations', 'gloss_details')
            ->first();

        if ($gloss === null) {
            return new Collection; // Emtpy collection, i.e. no gloss was found.
        }

        return new Collection([$gloss]);
    }

    /**
     * Gets the latest version of the gloss specified by the ID.
     *
     * @return GlossVersion
     */
    public function getLatestGlossVersion(int $glossId)
    {
        $version = GlossVersion::where('gloss_id', $glossId)
            ->with('translations', 'gloss_details', 'word')
            ->orderBy('created_at', 'desc') // order by latest
            ->orderBy('id', 'desc')
            ->first();

        return $version;
    }

    /**
     * Gets all versions of the gloss specified by the ID.
     *
     * @return Collection
     */
    public function getGlossVersions(int $glossId)
    {
        $versions = GlossVersion::where('gloss_id', $glossId)
            ->with('translations', 'gloss_details', 'word')
            ->orderBy('created_at', 'desc') // order by latest
            ->orderBy('id', 'desc')
            ->get();

        return new GlossVersionsValue([
            'versions' => $versions,
            'latest_version_id' => $versions->count() > 0 ? $versions->first()->id : null,
        ]);
    }

    public function getSpecificGlossVersion(int $versionId)
    {
        $version = GlossVersion::with('translations', 'gloss_details', 'word')
            ->find($versionId);

        return $version;
    }

    public function getGlossFromVersion(int $versionId)
    {
        $version = $this->getSpecificGlossVersion($versionId);
        if ($version === null) {
            return null;
        }

        $gloss = new Gloss($version->getAttributes());

        if (! $gloss->sense_id) {
            $sense = Gloss::where('id', $version->gloss_id)
                ->select('sense_id')
                ->first();
            if ($sense === null) {
                return null;
            }

            $gloss->sense_id = $sense->sense_id;
        }

        $gloss->id = $version->gloss_id;
        $gloss->exists = true;

        $gloss->load('account', 'gloss_group', 'language', 'sense', 'sense.word', 'speech', 'word');
        $gloss->setAttribute('translations', $version->translations->map(function ($t) {
            return new TranslationVersion($t->getAttributes());
        }));
        $gloss->setAttribute('gloss_details', $version->gloss_details->map(function ($d) {
            return new GlossDetail($d->getAttributes());
        }));

        return $gloss;
    }

    /**
     * Gets the latest gloss associated with the legay gloss ID.
     *
     * @param  int  $glossId  legacy gloss ID (pre-migration)
     * @return Gloss
     */
    public function getGlossVersionByPreMigrationId(int $glossId)
    {
        Log::warning('[DEPRECATED] Calling getGlossVersionByPreMigrationId for '.$glossId);
        $version = GlossVersion::where('__migration_gloss_id', $glossId)->first();

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
        $query = self::createGlossQueryWithoutDetails($fields, false);

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

    public function saveGloss(string $wordString, string $senseString, Gloss $gloss, array $translations, array $keywords, array $details = [], int &$changed = 0)
    {
        if (! $gloss instanceof Gloss) {
            throw new \Exception('Gloss must be an instance of the Gloss class.');
        }

        foreach ($translations as $translation) {
            if (! ($translation instanceof Translation)) {
                throw new \Exception('The array of translations must consist of instances of the Translation model.');
            }
        }

        foreach ($details as $detail) {
            if (! ($detail instanceof GlossDetail)) {
                throw new \Exception('The array of details must consist of instances of the GlossDetail model.');
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

        // All words should be lower case.
        $wordString = StringHelper::toLower($wordString);
        $senseString = StringHelper::toLower($senseString);

        // Retrieve existing or create a new word entity for the sense and the word.
        $word = $this->_wordRepository->save($wordString, $gloss->account_id);
        $senseWord = $this->_wordRepository->save($senseString, $gloss->account_id);

        // Load sense or create it if it doesn't exist. A sense is 1:1 mapped with
        // words, and therefore doesn't have its own incrementing identifier.
        $sense = $this->createSense($senseWord);

        // Generate a collection of keywords
        $translationStrings = array_map(function ($t) {
            return $t->translation;
        }, $translations);
        $newKeywords = array_unique(array_merge($keywords, [$wordString, $senseString], $translationStrings));
        sort($newKeywords);

        // These variables will be set to true if any changes are detected on the specified entity.
        $changed = GlossChange::NO_CHANGE->value;
        $isNew = false;

        // Load the original translation and update the translation's origin and parent columns.
        if (! $gloss->id && $gloss->external_id) {
            $existingGloss = Gloss::where('external_id', $gloss->external_id)->first();

            if ($existingGloss) {
                $gloss->id = $existingGloss->id;
                unset($existingGloss);
            }
        }

        if ($gloss->id) {
            $originalGloss = Gloss::with('sense', 'translations', 'word', 'keywords', 'gloss_details')
                ->findOrFail($gloss->id);

            // Detect changes to the following aspects of the gloss:
            // - info: any metadata about the gloss
            // - translations: English translation collection
            // - details: optional details collection
            // - keywords: generated search keywords
            //
            // The repository will only update the aspects that has been
            // modified.
            if ($originalGloss->sense_id !== $sense->id ||
                $originalGloss->word_id !== $word->id) {
                $changed |= GlossChange::WORD_OR_SENSE->value;
            }

            if (! $originalGloss->equals($gloss)) {
                $changed |= GlossChange::METADATA->value;
            }

            // If no other parameters have changed, iterate through the list of translations
            // and determine whether there are mismatching translations.
            //
            // Begin by checking the length of the two collections. Laravel collections are not
            // used by the repository, but is utilised by the Eloquent ORM, thus the different
            // syntax.
            if ($originalGloss->translations->count() !== count($translations)) {
                $changed |= GlossChange::TRANSLATIONS->value;
            }

            if (! ($changed & GlossChange::TRANSLATIONS->value)) {
                // If the length matches, iterate through each element and check whether they
                // exist in both collections.
                $existingTranslations = $originalGloss->translations->map(function ($t) {
                    return $t->translation;
                });
                $newTranslations = collect($translations)->map(function ($t) {
                    return $t->translation;
                });

                $existingTranslations->sort();
                $newTranslations->sort();

                if ($newTranslations != $existingTranslations) {
                    $changed |= GlossChange::TRANSLATIONS->value;
                }
            }

            // Gloss details changed?
            if ($originalGloss->gloss_details->count() !== count($details)) {
                $changed |= GlossChange::DETAILS->value;
            }

            if (! ($changed & GlossChange::DETAILS->value)) {
                foreach ($details as $d) {
                    if (! $originalGloss->gloss_details->contains(function ($od) use ($d) {
                        return $od->category === $d->category &&
                                $od->order === $d->order &&
                                $od->text === $d->text;
                    })) {
                        $changed |= GlossChange::DETAILS->value;
                        break;
                    }
                }
            }

            $oldKeywords = array_unique(
                $originalGloss->keywords->map(function ($f) {
                    return $f->keyword;
                })->toArray()
            );
            sort($oldKeywords);

            if ($newKeywords != $oldKeywords) {
                $changed |= GlossChange::KEYWORDS->value;
            }
        } else {
            $changed |= GlossChange::NEW->value;
        }

        if ($changed !== GlossChange::NO_CHANGE->value) {
            try {
                DB::beginTransaction();

                $isNew = $changed & GlossChange::NEW->value;

                if ($isNew || $changed & GlossChange::METADATA->value) {
                    $gloss->word_id = $word->id;
                    $gloss->sense_id = $sense->id;
                    $gloss->is_deleted = 0;
                    $gloss->has_details = count($details) > 0;
                    $gloss->save();
                    $gloss->refresh();
                } elseif ($changed & GlossChange::WORD_OR_SENSE->value) {
                    $gloss->word_id = $word->id;
                    $gloss->sense_id = $sense->id;
                    $gloss->save();
                    $gloss->refresh();
                }

                if ($isNew || $changed & GlossChange::DETAILS->value) {
                    if (! $isNew) {
                        $gloss->gloss_details()->delete();
                    }
                    $gloss->gloss_details()->saveMany($details);
                }

                if ($isNew || $changed & GlossChange::TRANSLATIONS->value) {
                    if (! $isNew) {
                        $gloss->translations()->delete();
                    }
                    $gloss->translations()->saveMany($translations);
                }

                if ($isNew || $changed & GlossChange::KEYWORDS->value) {
                    if (! $isNew) {
                        $gloss->keywords()->delete();
                    }

                    foreach ($newKeywords as $keyword) {
                        $keywordWord = $this->_wordRepository->save($keyword, $gloss->account_id);
                        $keywordLanguage = (
                            in_array($keyword, $translationStrings) || //
                            $senseString === $keyword
                        ) && $keyword !== $wordString //
                            ? $this->_systemLanguage : $gloss->language;
                        $this->_keywordRepository->createKeyword($keywordWord, $sense, $gloss, $keywordLanguage);
                    }
                }

                if ($changed !== GlossChange::NO_CHANGE->value) {
                    $this->saveVersion($gloss, $changed);
                }

                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }
        }

        // 13. Register an audit trail
        if ($changed !== GlossChange::NO_CHANGE->value) {
            $event = $isNew ? new GlossCreated($gloss, $gloss->account_id) //
                            : new GlossEdited($gloss, $this->_authManager->check() ? $this->_authManager->user()->id : $gloss->account_id);

            event($event);

            if ($isNew || $changed & GlossChange::KEYWORDS->value) {
                event(new SenseEdited($sense));
            }
        }

        return $gloss;
    }

    public function deleteGlossWithId(int $id, ?int $replaceId = null)
    {
        $gloss = Gloss::findOrFail($id);

        // Deleted glosses or deprecated (replaced) glosses cannot be deleted.
        if ($gloss->is_deleted) {
            return false;
        }

        $this->deleteGloss($gloss, $replaceId);

        return true;
    }

    /**
     * Gets keywords for the specified gloss.
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
     */
    protected static function createGlossQuery(int $languageId = 0, bool $includeOld = true, ?callable $whereCallback = null): Builder
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
            'w.normalized_word', 'g.created_at', 'g.updated_at', 'g.gloss_group_id', 'tg.name as gloss_group_name',
            'tg.is_canon', 'tg.is_old', 'tg.external_link_format', 'g.is_uncertain', 'g.external_id', 'g.is_rejected',
            'g.sense_id', 'tg.label as gloss_group_label', 'g.label', 'g.latest_gloss_version_id',
        ];

        $q0 = self::createGlossQueryWithoutDetails($columns, true)
            ->where($filters);

        if ($whereCallback != null) {
            $tmp = $whereCallback($q0);
            if ($tmp) {
                $q0 = $tmp;
            }
        }

        return $q0;
    }

    protected static function createGlossQueryWithoutDetails(array $columns, bool $addDetailsColumns)
    {
        $query = DB::table('glosses as g')
            ->join('translations as t', 'g.id', 't.gloss_id')
            ->join('accounts as a', 'g.account_id', 'a.id')
            ->join('words as w', 'g.word_id', 'w.id')
            ->leftJoin('gloss_groups as tg', 'g.gloss_group_id', 'tg.id')
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

    protected function deleteGloss(Gloss $g, ?int $replaceId = null)
    {
        $replacement = $replaceId ? Gloss::findOrFail($replaceId) : null;

        $g->keywords()->delete();

        // delete the sense if the specified gloss is the only gloss with that sense.
        if ($g->sense->glosses()->count() === 1 /* = $g */) {
            $g->sense->keywords()->delete();
        }

        if (! $replaceId !== null) {
            $g->sentence_fragments()->update([
                'gloss_id' => $replaceId,
            ]);

            $g->contributions()->update([
                'gloss_id' => null,
            ]);
        }

        $g->is_deleted = true;
        $g->save();

        event(new GlossDestroyed($g, $replacement, $this->_authManager->check() ? $this->_authManager->user()->id : 0));
    }

    protected function saveVersion(Gloss $gloss, int $changes)
    {
        // Make sure we're working with the latest version fo the gloss.
        $gloss->refresh();

        $data = $gloss->toArray();
        $data['gloss_id'] = $data['id'];
        $data['version_change_flags'] = $changes;
        unset($data['id']);

        try {
            DB::beginTransaction();

            $version = GlossVersion::create($data);
            $version->gloss_details()->saveMany(
                $gloss->gloss_details->map(function ($d) {
                    return new GlossDetailVersion($d->getAttributes());
                })
            );
            $version->translations()->saveMany(
                $gloss->translations->map(function ($t) {
                    return new TranslationVersion($t->getAttributes());
                })
            );

            $gloss->latest_gloss_version_id = $version->id;
            $gloss->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }
}
