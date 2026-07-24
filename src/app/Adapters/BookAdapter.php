<?php

namespace App\Adapters;

use App\Helpers\LexicalEntryAggregationHelper;
use App\Helpers\LinkHelper;
use App\Helpers\StringHelper;
use App\Interfaces\IMarkdownParser;
use App\Models\Gloss;
use App\Models\Language;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryDetail;
use App\Models\Versioning\LexicalEntryVersion;
use App\Repositories\Enumerations\LexicalEntryChange;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookAdapter
{
    private IMarkdownParser $_markdownParser;

    private LinkHelper $_linkHelper;

    public function __construct(IMarkdownParser $markdownParser, LinkHelper $linkHelper)
    {
        $this->_markdownParser = $markdownParser;
        $this->_linkHelper = $linkHelper;
    }

    /**
     * Transforms the specified lexical entries array to a view model.
     *
     * @param  array  $lexicalEntries  - the lexical entries should be an ordinary PHP object.
     * @param  Collection  $inflections  - an assocative array mapping lexical entries with inflections (optional)
     * @param  mixed  $commentsById  - an associative array mapping lexical entries with number of comments (optional)
     * @param  string|null  $word  - the search query yielding the specified list of lexical entries (optional)
     * @param  bool  $groupByLanguage  - declares whether the lexical entries should be sectioned up by language  (optional)
     * @param  bool  $atomDate  - ATOM format dates? (optional)
     * @return mixed - return value is determined by $groupByLanguage
     */
    public function adaptLexicalEntries(array $lexicalEntries, ?Collection $inflections = null, array $commentsById = [], ?string $word = null,
        bool $groupByLanguage = true, bool $atomDate = true)
    {
        $numberOfLexicalEntries = count($lexicalEntries);

        // * Optimize by dealing with some edge cases first
        //    - No lexical entry results
        if ($numberOfLexicalEntries < 1) {
            return [
                'word' => $word,
                'sections' => [],
                'single' => false,
                'sense' => [],
                'lead_with_unusual' => false,
            ];
        }

        $aggregator = new LexicalEntryAggregationHelper;
        $numberOfLexicalEntries = $aggregator->aggregate($lexicalEntries);

        //    - Just one translation result.
        if ($numberOfLexicalEntries === 1) {
            $lexicalEntry = $lexicalEntries[0];
            $languageIds = array_unique(array_merge(
                [$lexicalEntry->language_id],
                $this->collectReferencedLanguageIds([$lexicalEntry]),
            ));
            $allLanguages = Language::whereIn('id', $languageIds)->get();
            $language = $allLanguages->firstWhere('id', $lexicalEntry->language_id);

            return [
                'word' => $word,
                'sections' => [
                    [
                        // Load the language by examining the first (and only) element of the array
                        'language' => $language,
                        'entities' => [$this->adaptLexicalEntry($lexicalEntry, $allLanguages, $inflections, $commentsById, $atomDate)],
                    ],
                ],
                'languages' => $allLanguages,
                'single' => true,
                'sense' => [$lexicalEntry->sense_id],
                'lead_with_unusual' => false,
            ];
        }

        // * Multiple lexical entries (possibly across multiple languages)
        // Retrieve all applicable languages
        $languageIds = [];
        $entry2LanguageMap = $groupByLanguage ? [] : [[]];
        foreach ($lexicalEntries as $lexicalEntry) {
            if (! in_array($lexicalEntry->language_id, $languageIds)) {
                $languageIds[] = $lexicalEntry->language_id;

                if ($groupByLanguage) {
                    $entry2LanguageMap[$lexicalEntry->language_id] = [];
                }
            }
        }
        $languageIds = array_unique(array_merge($languageIds, $this->collectReferencedLanguageIds($lexicalEntries)));

        // Load the languages and order them by priority. The priority is configured by the Order field in the database.
        $allLanguages = Language::whereIn('id', $languageIds)
            ->orderByPriority()
            ->get();

        // Create a lexical entry to language map which will be used later to associate the lexical entries to their
        // languages. This is a necessary grouping operation due to the sort operation performed later on.
        $sense = [];
        $noOfSense = 0;
        foreach ($lexicalEntries as $lexicalEntry) {
            $adapted = $this->adaptLexicalEntry($lexicalEntry, $allLanguages, $inflections, $commentsById, $atomDate);
            if ($word !== null) {
                self::calculateRating($adapted, $word);
            }

            // adapt lexical entry for the view
            $entry2LanguageMap[$groupByLanguage ? $lexicalEntry->language_id : 0][] = $adapted;

            // Compose an array of senses in an ascending order.
            $senseId = $lexicalEntry->sense_id;
            if ($noOfSense === 0 || $senseId > $sense[$noOfSense - 1]) {
                $sense[] = $senseId;
                $noOfSense += 1;

            } elseif ($sense[$noOfSense - 1] !== $senseId) {
                for ($i = 0; $i < $noOfSense; $i += 1) {
                    // leave the loop and ignore the sense if it already exists
                    if ($sense[$i] === $senseId) {
                        break;
                    }

                    // if the current element is greater than the sense we would like to add to the collection,
                    // insert the sense at the current location (thus pushing the subsequent one forward).
                    if ($sense[$i] > $senseId) {
                        array_splice($sense, $i, 0, $senseId);
                        $noOfSense += 1;
                        break;
                    }
                }
            }
        }

        // Create a section array component for each language in the same order as the languages were retrieved from
        // the database
        if ($groupByLanguage) {
            // Sort languages by their highest entity rating (highest first)
            $languagesWithMaxRatings = [];
            foreach ($allLanguages as $language) {
                if (! array_key_exists($language->id, $entry2LanguageMap)) {
                    continue;
                }

                $entries = $entry2LanguageMap[$language->id];

                // Sort the entries based on their previously calculated rating.
                if ($word !== null) {
                    usort($entries, function ($a, $b) {
                        if ($a->rating < 0 && $b->rating < 0) {
                            $cmp = $a->rating < $b->rating ? -1 : ($a->rating === $b->rating ? 0 : 1);
                        } else {
                            $cmp = $a->rating > $b->rating ? -1 : ($a->rating === $b->rating ? 0 : 1);
                        }

                        if ($cmp !== 0) {
                            return $cmp;
                        }

                        $cmp = strnatcmp($a->word, $b->word);

                        return $cmp === 0 ? 0 : ($cmp < 0 ? -1 : 1);
                    });
                }

                $maxRating = max(array_column($entries, 'rating'));
                $languagesWithMaxRatings[] = [
                    'language' => $language,
                    'maxRating' => $maxRating,
                    'entries' => $entries,
                ];
            }

            // Sort by max rating (highest first), then by language order as fallback
            usort($languagesWithMaxRatings, function ($a, $b) {
                if ($a['maxRating'] !== $b['maxRating']) {
                    return $b['maxRating'] <=> $a['maxRating']; // Descending order
                }

                // Fallback to language order if ratings are equal
                return $a['language']->order <=> $b['language']->order;
            });

            $sections = [];
            foreach ($languagesWithMaxRatings as $languageData) {
                $sections[] = [
                    'language' => $languageData['language'],
                    'entities' => $languageData['entries'],
                ];
            }

            // Sections are grouped in the view into a "normal" block and, below a warning divider, an
            // "unusual" (older/rejected conceptual period) block - normally in that fixed order regardless
            // of rating. A language's unusual status never changes; what can change is which block the view
            // renders *first*. Lead with the unusual block only when the single best-rated entry overall
            // (the one being promoted to the top of the page) is itself a genuine direct match - i.e. it's
            // actually the right word, not just the least-bad fuzzy hit that happened to sort first.
            $topLanguageData = $languagesWithMaxRatings[0] ?? null;
            $topEntry = $topLanguageData['entries'][0] ?? null;
            $leadWithUnusual = $topLanguageData !== null
                && (bool) $topLanguageData['language']->is_unusual
                && ! empty($topEntry->is_direct_match);

            return [
                'word' => $word,
                'sections' => $sections,
                'languages' => $allLanguages,
                'single' => false,
                'sense' => $sense,
                'lead_with_unusual' => $leadWithUnusual,
            ];

        }

        return [
            'word' => $word,
            'sections' => [[ // <-- this is deliberate
                'language' => null,
                'entities' => $entry2LanguageMap[0],
            ]],
            'languages' => $allLanguages,
            'single' => false,
            'sense' => $sense,
            'lead_with_unusual' => false,
        ];
    }

    /**
     * Adapts the specified lexical entry for the view model. The lexical entry can either be an instance of the Eloquent LexicalEntry
     * entity class, or a plain PHP object (stdClass) generated by the Query Builder. The adapter creates the
     * following properties on the lexical entry: all_glosses (a string representation of the glosses relation),
     * language (a reference to the language object associated with the entity on stdClass only), inflections
     * (an array of inflections associated with the entity), comment_count (an integer representing the number of
     * comments associated with the entity). The method also formats the lexical entry's date properties accordingly.
     *
     * @param  LexicalEntry|stdClass  $lexicalEntry
     * @param  Collection  $languages  - an Eloquent collection of languages.
     * @param  Collection  $inflections  - an array of inflections with valid *lexical_entry_id*.
     * @param  array  $commentsById  - an associative array with the entity ID as key, and the number of comments as value.
     * @param  bool  $atomDate  - whether to format dates using the ATOM format.
     */
    public function adaptLexicalEntry($lexicalEntry, ?Collection $languages = null, ?Collection $inflections = null, array $commentsById = [],
        bool $atomDate = false, ?LinkHelper $linker = null): \stdClass
    {
        if ($linker === null) {
            $linker = $this->_linkHelper;
        }

        $separator = config('ed.gloss_translations_separator');

        if ($lexicalEntry instanceof LexicalEntry || $lexicalEntry instanceof LexicalEntryVersion) {
            $entity = $lexicalEntry;

            $lexicalEntry = (object) $lexicalEntry->attributesToArray();

            $lexicalEntry->account_name = $entity->account->nickname;
            $lexicalEntry->is_canon = $entity->lexical_entry_group_id ? $entity->lexical_entry_group->is_canon : null;
            $lexicalEntry->all_glosses = $entity->glosses->implode('translation', $separator);
            $lexicalEntry->word = $entity->word->word;
            $lexicalEntry->normalized_word = $entity->word->normalized_word;
            $lexicalEntry->type = $entity->speech_id ? $entity->speech->name : null;
            $lexicalEntry->lexical_entry_group_id = $entity->lexical_entry_group_id ?: null;
            $lexicalEntry->lexical_entry_group_label = $entity->lexical_entry_group_id ? $entity->lexical_entry_group->label : null;
            $lexicalEntry->lexical_entry_group_name = $entity->lexical_entry_group_id ? $entity->lexical_entry_group->name : null;
            $lexicalEntry->external_link_format = $entity->lexical_entry_group_id ? $entity->lexical_entry_group->external_link_format : null;
            $lexicalEntry->glosses = $entity->glosses->map(function ($g) {
                return new Gloss(['translation' => $g->translation]);
            });
            $lexicalEntry->lexical_entry_details = $entity->lexical_entry_details->map(function ($d) {
                return new LexicalEntryDetail([
                    'category' => $d->category,
                    'order' => $d->order,
                    'text' => $d->text,
                    'type' => $d->type,
                ]);
            });
            $lexicalEntry->derivations = $entity->relationLoaded('lexical_entry_derivations')
                ? $this->adaptDerivations($entity->lexical_entry_derivations, $linker)
                : [];
            $lexicalEntry->phonetic_developments = $entity->relationLoaded('lexical_entry_phonetic_developments')
                ? $this->adaptPhoneticDevelopments($entity->lexical_entry_phonetic_developments)
                : [];
            // Only populated for the single-entry view (LexicalEntryRepository::getLexicalEntry())
            // — the descendant tree can fan out widely for heavily-cited roots, so it's
            // deliberately not loaded for multi-entry glossary/search results.
            $lexicalEntry->derivatives = $entity->relationLoaded('descendant_derivation_rows')
                ? $this->adaptDerivatives($entity->descendant_derivation_rows, $entity->id, $linker)
                : ['children' => [], 'truncated' => false];

            unset(
                $lexicalEntry->word_id,
                $lexicalEntry->is_deleted,
                $lexicalEntry->speech_id,
                $lexicalEntry->has_details
            );

            if ($languages === null) {
                $languages = new Collection([$entity->language]);
            }

        } else {
            $lexicalEntry->all_glosses = $lexicalEntry->glosses->map(fn ($g) => $g->translation)->implode($separator);
            $lexicalEntry->derivations = property_exists($lexicalEntry, 'lexical_entry_derivations')
                ? $this->adaptDerivations($lexicalEntry->lexical_entry_derivations, $linker)
                : [];
            $lexicalEntry->phonetic_developments = property_exists($lexicalEntry, 'lexical_entry_phonetic_developments')
                ? $this->adaptPhoneticDevelopments($lexicalEntry->lexical_entry_phonetic_developments)
                : [];
            // Only carries rows when the caller opted into $includeDerivatives on
            // getLexicalEntriesWithDetails() (single/few-entry page loads) — empty for the
            // general multi-entry search flow, where the descendant tree isn't fetched at all.
            $lexicalEntry->derivatives = property_exists($lexicalEntry, 'lexical_entry_derivative_rows')
                ? $this->adaptDerivatives($lexicalEntry->lexical_entry_derivative_rows, $lexicalEntry->id, $linker)
                : ['children' => [], 'truncated' => false];
        }

        if (! empty($lexicalEntry->comments)) {
            $lexicalEntry->comments = $this->_markdownParser->parseMarkdownNoBlocks($lexicalEntry->comments);
        }

        // Restore the order of the details based on the `order` property
        $lexicalEntry->lexical_entry_details = $lexicalEntry->lexical_entry_details
            ->sort(fn ($a, $b) => $a->order === $b->order ? 0 : ($a->order > $b->order ? 1 : -1)
            )
            ->values(); // Reindex with sequential keys

        // Parse markdown to HTML
        $lexicalEntry->lexical_entry_details->each(function ($detail) {
            $detail->text = $this->_markdownParser->parseMarkdownNoBlocks($detail->text);
        });

        $lexicalEntry->account_url = $linker->author($lexicalEntry->account_id, $lexicalEntry->account_name);

        // Retrieve language reference to the specified lexical entry
        $lexicalEntry->language = $languages->first(function ($l) use ($lexicalEntry) {
            return $l->id === $lexicalEntry->language_id;
        }); // <-- infer success

        // Convert dates
        foreach (['created_at', 'updated_at'] as $dateField) {
            if (! property_exists($lexicalEntry, $dateField)) {
                continue;
            }

            if ($lexicalEntry->$dateField !== null && ! ($lexicalEntry->$dateField instanceof Carbon)) {
                $date = Carbon::parse($lexicalEntry->$dateField);

                if ($atomDate) {
                    $lexicalEntry->$dateField = $date->toAtomString();
                } else {
                    $lexicalEntry->$dateField = $date;
                }
            }
        }

        if (! property_exists($lexicalEntry, 'id')) {
            $lexicalEntry->id = null;

            return $lexicalEntry;
        }

        // Filter among the inflections, looking for references to the specified lexical entry.
        // The array is associative two-dimensional with the sentence fragment ID as the key, and an array containing
        // the  inflections associated with the fragment.
        $lexicalEntry->inflections = $inflections !== null && $inflections->has($lexicalEntry->id) ? $inflections[$lexicalEntry->id] : null;
        $lexicalEntry->comment_count = isset($commentsById[$lexicalEntry->id])
            ? $commentsById[$lexicalEntry->id] : 0;

        // Unversioned lexical entries are always the latest version
        $lexicalEntry->is_latest = true;

        // Create links upon the first element of each sentence fragment.
        if ($lexicalEntry->inflections !== null) {
            foreach ($lexicalEntry->inflections as $inflectionGroup) {
                if ($inflectionGroup[0]->sentence) {
                    // Use the linker to generate the URL
                    foreach ($inflectionGroup as $inflection) {
                        $inflection->sentence_url = $linker->sentence(
                            $inflection->language_id,
                            $inflection->language->name,
                            $inflection->sentence_id,
                            $inflection->sentence->name,
                            $inflection->sentence_id,
                            $inflection->sentence_fragment_id
                        );
                    }
                }

            }
        }

        return $lexicalEntry;
    }

    /**
     * Collects every language ID referenced by the specified lexical entries' derivation rows,
     * so the caller can widen its language lookup beyond just the entries' own languages — a
     * derivation's parent is frequently in a different (often older) language than the entry
     * itself. Handles both the Eloquent and query-builder stdClass row shapes, mirroring the
     * relationLoaded()/property_exists() pattern used in adaptLexicalEntry().
     */
    private function collectReferencedLanguageIds(array $lexicalEntries): array
    {
        $ids = [];
        foreach ($lexicalEntries as $lexicalEntry) {
            $derivations = $lexicalEntry instanceof LexicalEntry || $lexicalEntry instanceof LexicalEntryVersion
                ? ($lexicalEntry->relationLoaded('lexical_entry_derivations') ? $lexicalEntry->lexical_entry_derivations : collect())
                : ($lexicalEntry->lexical_entry_derivations ?? collect());

            foreach ($derivations as $d) {
                if ($d->parent_language_id) {
                    $ids[] = $d->parent_language_id;
                }
            }
        }

        return array_unique($ids);
    }

    /**
     * Groups derivation rows by hypothesis (derivation_group_uuid) and orders each chain from
     * the immediate parent towards the root. Resolved parents get a book-view URL; unresolved
     * ones (no matching lexical entry was imported for that ancestor) are left as plain text.
     */
    private function adaptDerivations(Collection $derivations, LinkHelper $linker): array
    {
        return $derivations
            ->sortBy('order')
            ->groupBy('derivation_group_uuid')
            ->map(function (Collection $chain) use ($linker) {
                return $chain->map(function ($d) use ($linker) {
                    $source = $d->source;
                    // Handle sources that has an identifier in the end, like
                    // Eldamo who suffixes their sources with . + numeric identifier
                    // like Let/426.0251
                    if (preg_match('/\.\d{4}$/', $source)) {
                        $source = substr($source, 0, strrpos($source, '.'));
                    }

                    $parentLexicalEntry = $d->parent_lexical_entry;

                    return [
                        'group_uuid' => $d->derivation_group_uuid,
                        'order' => $d->order,
                        'parent_form' => $d->parent_form,
                        'parent_word' => $parentLexicalEntry?->word?->word,
                        'parent_label' => $parentLexicalEntry?->label,
                        // Ancestors can carry several near-duplicate/overlapping senses recorded
                        // across different citations (e.g. one entry's glosses might read "grow,
                        // flourish, bloom" and "grow, flourish, bloom, thrive" from two sources) —
                        // joining all of them reads as garbled, doubled-up text. Eldamo itself only
                        // shows one representative sense per ancestor, so we do the same.
                        'parent_gloss' => $parentLexicalEntry?->glosses->first()?->translation,
                        'parent_language_id' => $d->parent_language_id,
                        'parent_lexical_entry_id' => $d->parent_lexical_entry_id,
                        'parent_url' => $d->parent_lexical_entry_id ? $linker->lexicalEntry($d->parent_lexical_entry_id) : null,
                        'is_uncertain' => (bool) $d->is_uncertain,
                        'is_rejected' => (bool) $d->is_rejected,
                        'source' => $source,
                        'comment' => $d->comment,
                        'intermediate_stages' => $d->intermediate_stages,
                    ];
                })->values();
            })
            ->values()
            ->all();
    }

    /**
     * Groups phonetic development rows by hypothesis (derivation_group_uuid) and orders each
     * chain from the earliest attested form towards the modern word.
     */
    private function adaptPhoneticDevelopments(Collection $phoneticDevelopments): array
    {
        return $phoneticDevelopments
            ->sortBy('order')
            ->groupBy('derivation_group_uuid')
            ->map(function (Collection $chain) {
                return $chain->map(function ($d) {
                    return [
                        'group_uuid' => $d->derivation_group_uuid,
                        'order' => $d->order,
                        'word' => $d->word,
                        'rule' => $d->rule,
                        'previous_word' => $d->previous_word,
                    ];
                })->values();
            })
            ->values()
            ->all();
    }

    /**
     * Folds derivation rows grouped by hypothesis (derivation_group_uuid) — every row of every
     * chain that names $rootLexicalEntryId as an ancestor at some order — into a merged tree of
     * $rootLexicalEntryId's descendants. Each group's own rows are the full ancestry chain of
     * one leaf/descendant word; the rows up to (and including) the one naming the root are the
     * path from the root down to that leaf. Where two leaves share an intermediate ancestor, the
     * corresponding tree nodes are merged (by parent_lexical_entry_id where resolved, else by
     * parent_form + parent_language_id) so the result is a real tree, not a flat list of paths —
     * matching Eldamo's own presentation, e.g. galad -> galadā -> {S.galadh, Q.alda, ...}.
     *
     * Capped at config('ed.book_derivatives_maximum_leaves') leaves (deterministically,
     * alphabetically by the leaf's own form) so a heavily-cited root can't produce an
     * unbounded tree.
     */
    private function adaptDerivatives(Collection $groupedRows, int $rootLexicalEntryId, LinkHelper $linker): array
    {
        $toNode = function ($row) use ($linker) {
            $lexicalEntryId = $row->parent_lexical_entry_id;
            $parentEntry = $row->parent_lexical_entry;

            return [
                'key' => $lexicalEntryId ? 'id:'.$lexicalEntryId : 'form:'.$row->parent_form.'|'.$row->parent_language_id,
                'form' => $row->parent_form,
                'word' => $parentEntry?->word?->word,
                'gloss' => $parentEntry?->glosses->first()?->translation,
                'source' => $parentEntry?->source,
                'language_id' => $row->parent_language_id,
                'lexical_entry_id' => $lexicalEntryId,
                'url' => $lexicalEntryId ? $linker->lexicalEntry($lexicalEntryId) : null,
                'is_word' => (bool) $lexicalEntryId,
            ];
        };

        $paths = [];
        foreach ($groupedRows as $chain) {
            $chain = $chain->sortBy('order')->values();
            $hit = $chain->firstWhere('parent_lexical_entry_id', $rootLexicalEntryId);
            if ($hit === null) {
                continue;
            }

            // Rows below the hit's order describe the nodes strictly between the root and the
            // leaf (nearest-to-root first); the hit row itself only confirms the root's identity,
            // which we already know, so it contributes no node of its own.
            $intermediateNodes = $chain
                ->filter(fn ($row) => $row->order < $hit->order)
                ->sortByDesc('order')
                ->map($toNode)
                ->values()
                ->all();

            $leafEntry = $chain->first()->lexical_entry;
            if ($leafEntry === null) {
                continue;
            }

            $leafNode = [
                'key' => 'id:'.$leafEntry->id,
                'form' => $leafEntry->word->word,
                'word' => $leafEntry->word->word,
                'gloss' => $leafEntry->glosses->first()?->translation,
                'source' => $leafEntry->source,
                'language_id' => $leafEntry->language_id,
                'lexical_entry_id' => $leafEntry->id,
                'url' => $linker->lexicalEntry($leafEntry->id),
                'is_word' => true,
            ];

            $paths[] = [...$intermediateNodes, $leafNode];
        }

        if (empty($paths)) {
            return ['children' => [], 'truncated' => false];
        }

        $maxLeaves = config('ed.book_derivatives_maximum_leaves');
        usort($paths, fn ($a, $b) => strcasecmp(end($a)['form'], end($b)['form']));
        $truncated = count($paths) > $maxLeaves;
        $paths = array_slice($paths, 0, $maxLeaves);

        $tree = [];
        foreach ($paths as $path) {
            $level = &$tree;
            foreach ($path as $node) {
                if (! isset($level[$node['key']])) {
                    $level[$node['key']] = $node + ['children' => []];
                }
                $level = &$level[$node['key']]['children'];
            }
            unset($level);
        }

        return [
            'children' => $this->reindexDerivativeTree($tree),
            'truncated' => $truncated,
        ];
    }

    /**
     * The tree built in adaptDerivatives() is keyed by merge-key (a string) at every level, so
     * json_encode() would serialize any node with children as a JSON object instead of an array
     * unless every level is re-indexed, not just the top one.
     */
    private function reindexDerivativeTree(array $childrenByKey): array
    {
        return array_values(array_map(
            fn ($node) => [...$node, 'children' => $this->reindexDerivativeTree($node['children'])],
            $childrenByKey,
        ));
    }

    public function adaptLexicalEntryVersions(Collection $values, int $latestVersionId)
    {
        $word = null;
        $versions = [];
        if ($values->count() > 0) {
            $word = $values->first()->word->word;

            $model = $this->adaptLexicalEntries($values->all(), collect([]), [], $word, false);
            $versions = $model['sections'][0]['entities'];
            unset($model);

            foreach ($versions as $version) {
                $version->_is_latest = $version->id === $latestVersionId;

                $changes = [];
                foreach (LexicalEntryChange::cases() as $change) {
                    if ($change->value & $version->version_change_flags) {
                        $changes[] = trans('glossary.changes.'.$change->name);
                    }
                }
                $version->_recorded_changes = $changes;
            }
        }

        return [
            'word' => $word,
            'versions' => $versions,
        ];
    }

    /**
     * Estimates how relevant the specified lexical entry object is based on the search term.
     * Improved implementation that considers all relevant fields with proper weighting.
     */
    public static function calculateRating(\stdClass $lexicalEntry, string $word)
    {
        if (empty($word)) {
            $lexicalEntry->is_direct_match = false;

            return 1 << 31;
        }

        $rating = 0;
        $normalizedWord = StringHelper::normalize($word);
        $searchTerms = self::extractSearchTerms($word);

        // 1. WORD FIELD (highest priority - exact word matches)
        $wordRating = self::calculateWordFieldRating($lexicalEntry->word, $normalizedWord, $searchTerms);
        $rating += $wordRating * 1000000; // Highest weight

        // 2. TRANSLATIONS FIELD (high priority - English translations)
        $translationRating = self::calculateTranslationFieldRating($lexicalEntry->glosses, $normalizedWord, $searchTerms);
        $rating += $translationRating * 100000; // High weight

        // A "direct match" is a genuine substring/exact hit on the word or its translation - as opposed to
        // a fuzzy similarity match or a hit buried in comments/details/source. Used to decide whether an
        // "unusual" language's result is good enough to bypass the older-languages grouping (see
        // adaptLexicalEntries).
        $lexicalEntry->is_direct_match = $wordRating >= 80 || $translationRating >= 65;

        // 3. COMMENTS FIELD (medium priority - rich context)
        if (! empty($lexicalEntry->comments)) {
            $commentRating = self::calculateCommentFieldRating($lexicalEntry->comments, $normalizedWord, $searchTerms);
            $rating += $commentRating * 10000; // Medium weight
        }

        // 4. GLOSS DETAILS (lower priority - additional context)
        if (! empty($lexicalEntry->lexical_entry_details)) {
            $detailsRating = self::calculateDetailsFieldRating($lexicalEntry->lexical_entry_details, $normalizedWord, $searchTerms);
            $rating += $detailsRating * 1000; // Lower weight
        }

        // 5. SOURCE FIELD (lowest priority - metadata)
        if (! empty($lexicalEntry->source)) {
            $sourceRating = self::calculateSourceFieldRating($lexicalEntry->source, $normalizedWord, $searchTerms);
            $rating += $sourceRating * 100; // Lowest weight
        }

        // Default rating for keyword matches (very low priority)
        if ($rating === 0) {
            $rating = 10;
        }

        // For uncertain/non-canon lexical entries, rank them at the lower end of their matching field's score range
        // This keeps them in results but deprioritizes them compared to certain/canon lexical entries
        if (! $lexicalEntry->is_canon || $lexicalEntry->is_uncertain) {
            // Reduce the rating to the lower end of the score range
            // This ensures they appear after certain/canon lexical entries but still in relevant results
            $rating = max(1, $rating * 0.1); // Reduce to 10% of original score
        }

        $lexicalEntry->rating = $rating;
    }

    /**
     * Extract meaningful search terms from the input word
     */
    private static function extractSearchTerms(string $word): array
    {
        $terms = [];

        // Add the original word
        $terms[] = $word;

        // Add normalized version
        $terms[] = StringHelper::normalize($word);

        // Add lowercase version
        $terms[] = strtolower($word);

        // Add word without diacritics (if any)
        $terms[] = self::removeDiacritics($word);

        return array_unique(array_filter($terms));
    }

    /**
     * Calculate rating for the word field (highest priority)
     */
    private static function calculateWordFieldRating(string $lexicalEntryWord, string $normalizedWord, array $searchTerms): int
    {
        $rating = 0;
        $normalizedLexicalEntryWord = StringHelper::normalize($lexicalEntryWord);

        foreach ($searchTerms as $term) {
            // Exact match (highest score)
            if (strcasecmp($lexicalEntryWord, $term) === 0) {
                $rating = max($rating, 100);

                continue;
            }

            // Normalized exact match
            if (strcasecmp($normalizedLexicalEntryWord, $term) === 0) {
                $rating = max($rating, 95);

                continue;
            }

            // Starts with match
            if (stripos($lexicalEntryWord, $term) === 0) {
                $rating = max($rating, 80);

                continue;
            }

            // Contains match
            if (stripos($lexicalEntryWord, $term) !== false) {
                $rating = max($rating, 60);

                continue;
            }

            // Similarity match
            $percent = 0;
            similar_text($normalizedLexicalEntryWord, $term, $percent);
            if ($percent > 70) {
                $rating = max($rating, $percent);
            }
        }

        return $rating;
    }

    /**
     * Calculate rating for the translations field
     */
    private static function calculateTranslationFieldRating(Collection $glosses, string $normalizedWord, array $searchTerms): int
    {
        $maxRating = 0;

        foreach ($glosses as $gloss) {
            $glossText = $gloss->translation;
            $normalizedGlossText = StringHelper::normalize($glossText);

            foreach ($searchTerms as $term) {
                // Exact match
                if (strcasecmp($glossText, $term) === 0) {
                    $maxRating = max($maxRating, 90);

                    continue;
                }

                // Normalized exact match
                if (strcasecmp($normalizedGlossText, $term) === 0) {
                    $maxRating = max($maxRating, 85);

                    continue;
                }

                // Word boundary match (check if term is a complete word)
                if (preg_match('/\b'.preg_quote($term, '/').'\b/i', $glossText)) {
                    $maxRating = max($maxRating, 75);

                    continue;
                }

                // Starts with match
                if (stripos($glossText, $term) === 0) {
                    $maxRating = max($maxRating, 65);

                    continue;
                }

                // Contains match
                if (stripos($glossText, $term) !== false) {
                    $maxRating = max($maxRating, 50);

                    continue;
                }

                // Similarity match
                $percent = 0;
                similar_text($normalizedGlossText, $term, $percent);
                if ($percent > 60) {
                    $maxRating = max($maxRating, $percent * 0.8);
                }
            }
        }

        return $maxRating;
    }

    /**
     * Calculate rating for the comments field
     */
    private static function calculateCommentFieldRating(string $comments, string $normalizedWord, array $searchTerms): int
    {
        $rating = 0;
        $normalizedComments = StringHelper::normalize($comments);

        foreach ($searchTerms as $term) {
            // Word boundary match (most relevant in comments)
            if (preg_match('/\b'.preg_quote($term, '/').'\b/i', $comments)) {
                $rating = max($rating, 70);

                continue;
            }

            // Contains match
            if (stripos($comments, $term) !== false) {
                $rating = max($rating, 40);

                continue;
            }

            // Normalized contains match
            if (stripos($normalizedComments, $term) !== false) {
                $rating = max($rating, 35);

                continue;
            }

            // Similarity match (lower weight for comments)
            $percent = 0;
            similar_text($normalizedComments, $term, $percent);
            if ($percent > 50) {
                $rating = max($rating, $percent * 0.5);
            }
        }

        return $rating;
    }

    /**
     * Calculate rating for the gloss details field
     */
    private static function calculateDetailsFieldRating(Collection $lexicalEntryDetails, string $normalizedWord, array $searchTerms): int
    {
        $maxRating = 0;

        foreach ($lexicalEntryDetails as $detail) {
            $detailText = $detail->text;
            $normalizedDetailText = StringHelper::normalize($detailText);

            foreach ($searchTerms as $term) {
                // Word boundary match
                if (preg_match('/\b'.preg_quote($term, '/').'\b/i', $detailText)) {
                    $maxRating = max($maxRating, 60);

                    continue;
                }

                // Contains match
                if (stripos($detailText, $term) !== false) {
                    $maxRating = max($maxRating, 30);

                    continue;
                }

                // Similarity match
                $percent = 0;
                similar_text($normalizedDetailText, $term, $percent);
                if ($percent > 40) {
                    $maxRating = max($maxRating, $percent * 0.4);
                }
            }
        }

        return $maxRating;
    }

    /**
     * Calculate rating for the source field
     */
    private static function calculateSourceFieldRating(string $source, string $normalizedWord, array $searchTerms): int
    {
        $rating = 0;
        $normalizedSource = StringHelper::normalize($source);

        foreach ($searchTerms as $term) {
            // Contains match
            if (stripos($source, $term) !== false) {
                $rating = max($rating, 20);

                continue;
            }

            // Similarity match (very low weight for source)
            $percent = 0;
            similar_text($normalizedSource, $term, $percent);
            if ($percent > 30) {
                $rating = max($rating, $percent * 0.2);
            }
        }

        return $rating;
    }

    /**
     * Remove diacritics from a string
     */
    private static function removeDiacritics(string $text): string
    {
        $text = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ì', 'ò', 'ù', 'â', 'ê', 'î', 'ô', 'û', 'ä', 'ë', 'ï', 'ö', 'ü', 'ñ', 'ç'],
            ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'c'],
            $text
        );

        return $text;
    }
}
