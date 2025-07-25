<?php

namespace App\Adapters;

use App\Helpers\GlossAggregationHelper;
use App\Helpers\LinkHelper;
use App\Helpers\StringHelper;
use App\Interfaces\IMarkdownParser;
use App\Models\Gloss;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryDetail;
use App\Models\LexicalEntryGroup;
use App\Models\Language;
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

        // Reverses phonetic approximations
        if ($word !== null) {
            $word = StringHelper::reverseNormalization($word);
        }

        // * Optimize by dealing with some edge cases first
        //    - No lexical entry results
        if ($numberOfLexicalEntries < 1) {
            return [
                'word' => $word,
                'sections' => [],
                'single' => false,
                'sense' => [],
            ];
        }

        $aggregator = new GlossAggregationHelper;
        $numberOfLexicalEntries = $aggregator->aggregate($lexicalEntries);

        //    - Just one translation result.
        if ($numberOfLexicalEntries === 1) {
            $lexicalEntry = $lexicalEntries[0];
            $language = Language::findOrFail($lexicalEntry->language_id);

            return [
                'word' => $word,
                'sections' => [
                    [
                        // Load the language by examining the first (and only) element of the array
                        'language' => $language,
                        'entities' => [$this->adaptLexicalEntry($lexicalEntry, new Collection([$language]), $inflections, $commentsById, $atomDate)],
                    ],
                ],
                'single' => true,
                'sense' => [$lexicalEntry->sense_id],
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
            $sections = [];
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

                $sections[] = [
                    'language' => $language,
                    'entities' => $entries,
                ];
            }

            return [
                'word' => $word,
                'sections' => $sections,
                'languages' => null,
                'single' => false,
                'sense' => $sense,
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
            $lexicalEntry->all_glosses = $lexicalEntry->glosses->map(fn($g) => $g->translation)->implode($separator);
        }

        if (! empty($lexicalEntry->comments)) {
            $lexicalEntry->comments = $this->_markdownParser->parseMarkdownNoBlocks($lexicalEntry->comments);
        }

        // Restore the order of the details based on the `order` property
        $lexicalEntry->lexical_entry_details->sort(fn ($a, $b) =>
            $a->order === $b->order ? 0 : ($a->order > $b->order ? 1 : -1),
        );

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

        // 3. COMMENTS FIELD (medium priority - rich context)
        if (!empty($lexicalEntry->comments)) {
            $commentRating = self::calculateCommentFieldRating($lexicalEntry->comments, $normalizedWord, $searchTerms);
            $rating += $commentRating * 10000; // Medium weight
        }

        // 4. GLOSS DETAILS (lower priority - additional context)
        if (!empty($lexicalEntry->lexical_entry_details)) {
            $detailsRating = self::calculateDetailsFieldRating($lexicalEntry->lexical_entry_details, $normalizedWord, $searchTerms);
            $rating += $detailsRating * 1000; // Lower weight
        }

        // 5. SOURCE FIELD (lowest priority - metadata)
        if (!empty($lexicalEntry->source)) {
            $sourceRating = self::calculateSourceFieldRating($lexicalEntry->source, $normalizedWord, $searchTerms);
            $rating += $sourceRating * 100; // Lowest weight
        }

        // Default rating for keyword matches (very low priority)
        if ($rating === 0) {
            $rating = 10;
        }

        // For uncertain/non-canon lexical entries, rank them at the lower end of their matching field's score range
        // This keeps them in results but deprioritizes them compared to certain/canon lexical entries
        if (!$lexicalEntry->is_canon || $lexicalEntry->is_uncertain) {
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
                if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $glossText)) {
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
            if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $comments)) {
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
                if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $detailText)) {
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
