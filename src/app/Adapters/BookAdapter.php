<?php

namespace App\Adapters;

use Illuminate\Support\Collection;
use Carbon\Carbon;

use App\Helpers\{
    LinkHelper, StringHelper, MarkdownParser
};
use App\Models\{
    Account, 
    Gloss, 
    Language,
    Translation
};

class BookAdapter
{
    /**
     * Transforms the specified glosses array to a view model.
     *
     * @param array $glosses - the glosses should be an ordinary PHP object.
     * @param array $inflections - an assocative array mapping glosses with inflections (optional)
     * @param mixed $commentsById - an associative array mapping glosses with number of comments (optional)
     * @param string|null $word - the search query yielding the specified list of glosses (optional)
     * @param bool $groupByLanguage - declares whether the glosses should be sectioned up by language  (optional)
     * @param bool $atomDate - ATOM format dates? (option)
     * @return mixed - return value is determined by $groupByLanguage
     */
    public function adaptGlosses(array $glosses, array $inflections = [], array $commentsById = [], string $word = null, 
        bool $groupByLanguage = true, bool $atomDate = true)
    {
        $numberOfGlosses = count($glosses);

        // Reverses phonetic approximations  
        if ($word !== null) {
            $word = StringHelper::reverseNormalization($word);
        }
        
        // * Optimize by dealing with some edge cases first
        //    - No gloss results
        if ($numberOfGlosses < 1) {
            return [
                'word' => $word,
                'sections' => [],
                'single' => false,
                'sense' => []
            ];
        }

        usort($glosses, function ($a, $b) {
            return $a->id === $b->id ? 0 : ($a->id < $b->id ? -1 : 1);
        });

        // brief interlude - convert markdown to HTML and generate author URLs, 
        // and merge translations, where applicable.
        $linker = new LinkHelper();
        $previousGloss = null;
        $i = 0;

        // NOTE: this loop assumes that the collection is in fact ordered by the 
        //       glosses' ID.
        while ($i < $numberOfGlosses) {
            $gloss = $glosses[$i];

            if ($gloss instanceOf Gloss) {
                // Gloss model entities already have the 'translations' relation, and would therefore
                // only requiring eager loading. This is not performant to be doing in the adapter, and 
                // therefore raises an error.
                if (! $gloss->relationLoaded('translations')) {
                    throw new \Exception('Failed to adapt gloss '.$gloss->id.' because its relation "translations" is not loaded.');
                }

            } else {
                if ($previousGloss === null || $previousGloss->id !== $gloss->id) {
                    // Glosses do not match, so start over by assigning the current gloss
                    // an array of translations.
                    $gloss->translations = [
                        new Translation(['translation' => $gloss->translation])
                    ];
                    unset($gloss->translation);

                    $previousGloss = $gloss;

                } else if ($previousGloss->id === $gloss->id) {
                    $previousGloss->translations[] = new Translation([
                        'translation' => $gloss->translation
                    ]);

                    array_splice($glosses, $i, 1);
                    $numberOfGlosses -= 1;
                    continue;
                }
            }

            $i += 1;
        }

        //    - Just one translation result.
        if ($numberOfGlosses === 1) {
            $gloss = $glosses[0];
            $language = Language::findOrFail($gloss->language_id);

            return self::assignColumnWidths([
                'word' => $word,
                'sections' => [
                    [
                        // Load the language by examining the first (and only) element of the array
                        'language' => $language,
                        'glosses'  => [ $this->adaptGloss($gloss, new Collection([$language]), $inflections, $commentsById, $atomDate, $linker) ]
                    ]
                ],
                'single' => true,
                'sense' => [$gloss->sense_id]
            ], 1);
        }

        // * Multiple glosses (possibly across multiple languages)
        // Retrieve all applicable languages
        $languageIds = [];
        $gloss2LanguageMap = $groupByLanguage ? [] : [[]];
        foreach ($glosses as $gloss) {
            if (! in_array($gloss->language_id, $languageIds)) {
                $languageIds[] = $gloss->language_id;

                if ($groupByLanguage) {
                    $gloss2LanguageMap[$gloss->language_id] = [];
                }
            }
        }

        // Load the languages and order them by priority. The priority is configured by the Order field in the database.
        $allLanguages = Language::whereIn('id', $languageIds)
            ->orderByPriority()
            ->get();

        // Create a gloss to language map which will be used later to associate the glosses to their
        // languages. This is a necessary grouping operation due to the sort operation performed later on.
        $sense = [];
        $noOfSense = 0;
        foreach ($glosses as $gloss) {
            $adapted = $this->adaptGloss($gloss, $allLanguages, $inflections, $commentsById, $atomDate, $linker);
            if ($word !== null) {
                self::calculateRating($adapted, $word);
            }

            // adapt gloss for the view
            $gloss2LanguageMap[$groupByLanguage ? $gloss->language_id : 0][] = $adapted;
            
            // Compose an array of senses in an ascending order.
            $senseId = $gloss->sense_id;
            if ($noOfSense === 0 || $senseId > $sense[$noOfSense - 1]) {
                $sense[] = $senseId;
                $noOfSense += 1;

            } else if ($sense[$noOfSense - 1] !== $senseId) {
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

                if (! array_key_exists($language->id, $gloss2LanguageMap)) {
                    continue;
                }

                $glosses = $gloss2LanguageMap[$language->id];

                // Sort the glosses based on their previously calculated rating.
                if ($word !== null) {
                    usort($glosses, function ($a, $b) {
                        return $a->rating > $b->rating ? -1 : ($a->rating === $b->rating ? 0 : 1);
                    });
                }

                $sections[] = [
                    'language' => $language,
                    'glosses' => $glosses
                ];
            }

            return self::assignColumnWidths([
                'word'      => $word,
                'sections'  => $sections,
                'languages' => null,
                'single'    => false,
                'sense'     => $sense
            ], count($allLanguages));

        } 

        return [
            'word'    => $word,
            'sections' => [[ // <-- this is deliberate
                'language' => null,
                'glosses'  => $gloss2LanguageMap[0]
            ]],
            'languages' => $allLanguages,
            'single'    => false,
            'sense'     => $sense
        ];
    }

    /**
     * Adapts the specified gloss for the view model. The gloss can either be an instance of the Eloquent Gloss
     * entity class, or a plain PHP object (stdClass) generated by the Query Builder. The adapter creates the 
     * following properties on the gloss: all_translations (a string representation of the translations relation),
     * language (a reference to the language object associated with the entity on stdClass only), inflections
     * (an array of inflections associated with the entity), comment_count (an integer representing the number of
     * comments associated with the entity). The method also formats the gloss's date properties accordingly.
     *
     * @param Gloss|stdClass $gloss
     * @param Collection $languages - an Eloquent collection of languages.
     * @param array $inflections - an array of inflections with valid *gloss_id*.
     * @param array $commentsById - an associative array with the entity ID as key, and the number of comments as value.
     * @param bool $atomDate - whether to format dates using the ATOM format.
     * @param LinkHelper $linker
     * @return void
     */
    public function adaptGloss($gloss, Collection $languages = null, array $inflections = [], array $commentsById = [], 
        bool $atomDate = false, LinkHelper $linker = null) 
    {
        if ($linker === null) {
            $linker = new LinkHelper();
        }

        $isGlossEntity = $gloss instanceof Gloss;
        $separator = config('ed.gloss_translations_separator');

        if ($isGlossEntity) {
            $entity = $gloss;

            $gloss = (object) $gloss->attributesToArray();

            $gloss->account_name         = $entity->account->nickname;
            $gloss->is_canon             = $entity->gloss_group_id ? $entity->gloss_group->is_canon : null;
            $gloss->all_translations     = $entity->translations->implode('translation', $separator);
            $gloss->word                 = $entity->word->word;
            $gloss->normalized_word      = $entity->word->normalized_word;
            $gloss->type                 = $entity->speech_id ? $entity->speech->name : null;
            $gloss->gloss_group_id       = $entity->gloss_group_id ?: null;
            $gloss->gloss_group_name     = $entity->gloss_group_id ? $entity->gloss_group->name : null;
            $gloss->external_link_format = $entity->gloss_group_id ? $entity->gloss_group->external_link_format : null;
            $gloss->translations         = $entity->translations->map(function ($t) {
                return new Translation(['translation' => $t->translation]);
            })->all();

            unset(
                $gloss->word_id, 
                $gloss->is_deleted,
                $gloss->child_gloss_id,
                $gloss->updated_at,
                $gloss->speech_id
            );

            if ($languages === null) {
                $languages = new Collection([$entity->language]);
            }

        } else {
            $gloss->all_translations = implode($separator, array_map(function ($t) {
                return $t->translation;
            }, $gloss->translations));
        }

        if (!empty($gloss->comments)) {
            $markdownParser = new MarkdownParser(['>', '#']);
            $gloss->comments = $markdownParser->text($gloss->comments);
        }

        $gloss->account_url = $linker->author($gloss->account_id, $gloss->account_name);

        // Retrieve language reference to the specified gloss
        $gloss->language = $languages->first(function ($l) use($gloss) {
            return $l->id === $gloss->language_id;
        }); // <-- infer success

        // Convert dates
        if (! ($gloss->created_at instanceof Carbon)) {
            $date = Carbon::parse($gloss->created_at);
            
            if ($atomDate) {
                $gloss->created_at = $date->toAtomString();
            } else {
                $gloss->created_at = $date;
            }
        }

        if (! property_exists($gloss, 'id')) {
            $gloss->id = null;
            return $gloss;
        }

        // Filter among the inflections, looking for references to the specified gloss.
        // The array is associative two-dimensional with the sentence fragment ID as the key, and an array containing
        // the  inflections associated with the fragment.
        $inflectionsForGloss = array_filter($inflections, function ($i) use($gloss) {
            return $i[0]->gloss_id === $gloss->id;
        });
        $gloss->inflections = count($inflectionsForGloss) > 0 
            ? $inflectionsForGloss : null;
        $gloss->comment_count = isset($commentsById[$gloss->id]) 
            ? $commentsById[$gloss->id] : 0;

        // Create links upon the first element of each sentence fragment.
        if ($gloss->inflections !== null) {
            foreach ($gloss->inflections as $sentenceFragmentId => $inflectionsForFragment) {

                // The [0] restricts the URL to the first element in the array. 
                if (! isset($inflectionsForFragment[0]->sentence_url)) {
                    // Use the linker to generate the URL
                    $inflectionsForFragment[0]->sentence_url = $linker->sentence(
                        $inflectionsForFragment[0]->language_id, 
                        $inflectionsForFragment[0]->language_name, 
                        $inflectionsForFragment[0]->sentence_id, 
                        $inflectionsForFragment[0]->sentence_name,
                        $sentenceFragmentId
                    );
                }
                
            }
        }

        return $gloss;
    }

    /**
     * Estimates how relevant the specified gloss object is based on the search term.
     * @param $gloss
     * @param $word
     */
    private static function calculateRating(\stdClass $gloss, string $word)
    {
        if (empty($word)) {
            return 1 << 31;
        }

        $rating = 0;

        // First, check if the gloss contains the search term by looking for its
        // position within the word property, albeit normalized.
        $n = StringHelper::normalize($gloss->word);
        $nw = StringHelper::normalize($word);
        $lengthOfN = mb_strlen($n);
        $pos = strpos($n, $nw);

        if ($pos !== false) {
            // The "cleaner" the match, the better
            $rating = 100000 + ($pos * -1) * 10;

            if ($pos === 0 && $n === $nw) {
                $rating *= 2;
            }
        }

        // If the previous check failed, check for the glosss field. Statistically,
        // this is the most common case.
        $pos = strpos($n, $word);
        $maxRating = 0;
        foreach ($gloss->translations as $t) {
            $n0 = StringHelper::normalize($t->translation);
            $pos = strpos($n0, $nw);

            // an exact match?
            if ($pos === 0) {
                $maxRating = 90000;
                break;
            } else if ($pos > 0) {
                $lengthOfN0 = mb_strlen($n0);
                $maxRating = max($maxRating, min(80000, 10000 + ($lengthOfN0 - $pos) * 1000));
            }
        }
        $rating += $maxRating;

        // If the previous check failed, check within the comments field. Statistically,
        // this is an uncommon match.
        if ($gloss->comments !== null) {
            $n = $gloss->comments;
            $pos = strpos($n, $word);

            if ($pos !== false) {
                $rating += 1000;
            }
        }

        // Default rating for all other cases, probably matches by keyword.
        if ($rating === 0) {
            $rating = 100;
        }

        // Bump all unverified glosses to a trailing position
        if (! $gloss->is_canon) {
            $rating = (1 << 31) + $rating;
        }

        $gloss->rating = $rating;
    }

    private static function assignColumnWidths(array $model, int $numberOfLanguages)
    {
        $max = 12;
        $mid = $numberOfLanguages > 1 ? 6 : $max;
        $min = $numberOfLanguages > 2 ? 6 : $mid;

        $model['columnsMax'] = $max;
        $model['columnsMid'] = $mid;
        $model['columnsMin'] = $min;

        return $model;
    }
}
