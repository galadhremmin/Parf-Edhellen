<?php

namespace App\Adapters;
use App\Helpers\{
    LinkHelper, StringHelper, MarkdownParser
};
use App\Models\{
    Language, Translation, Account
};
use Illuminate\Support\Collection;
use Carbon\Carbon;

class BookAdapter
{
    /**
     * Transforms the specified translations array to a view model.
     *
     * @param array $translations
     * @param array $inflections - an assocative array mapping translations with inflections (optional)
     * @param mixed $commentsById - an associative array mapping translations with number of comments (optional)
     * @param string|null $word - the search query yielding the specified list of translations (optional)
     * @param bool $groupByLanguage - declares whether the translations should be sectioned up by language  (optional)
     * @param bool $atomDate - ATOM format dates? (option)
     * @return mixed - return value is determined by $groupByLanguage
     */
    public function adaptTranslations(array $translations, array $inflections = [], array $commentsById = [], string $word = null, 
        bool $groupByLanguage = true, bool $atomDate = true)
    {
        $numberOfTranslations = count($translations);

        // Reverses phonetic approximations  
        if ($word !== null) {
            $word = StringHelper::reverseNormalization($word);
        }
        
        // * Optimize by dealing with some edge cases first
        //    - No translation results
        if ($numberOfTranslations < 1) {
            return [
                'word' => $word,
                'sections' => [],
                'single' => false,
                'sense' => []
            ];
        }

        // brief interlude - convert markdown to HTML and generate author URLs
        $markdownParser = new MarkdownParser(['>', '#']);
        $linker = new LinkHelper();
        $authorUrls = [];

        foreach ($translations as $translation) {
            if (!empty($translation->comments)) {
                $translation->comments = $markdownParser->text($translation->comments);
            }

            if (!isset($authorUrls[$translation->account_id])) {
                $authorUrls[$translation->account_id] = $linker->author($translation->account_id, $translation->account_name);
            }

            $translation->account_url = $authorUrls[$translation->account_id];
        }

        //    - Just one translation result.
        if ($numberOfTranslations === 1) {
            $translation = $translations[0];
            $language = Language::findOrFail($translation->language_id);

            return self::assignColumnWidths([
                'word' => $word,
                'sections' => [
                    [
                        // Load the language by examining the first (and only) element of the array
                        'language' => $language,
                        'glosses'  => [ self::adaptTranslation($translation, new Collection([$language]), $inflections, $commentsById, $atomDate, $linker) ]
                    ]
                ],
                'single' => true,
                'sense' => [$translation->sense_id]
            ], 1);
        }

        // * Multiple translations (possibly across multiple languages)
        // Retrieve all applicable languages
        $languageIds = [];
        $gloss2LanguageMap = $groupByLanguage ? [] : [[]];
        foreach ($translations as $translation) {
            if (! in_array($translation->language_id, $languageIds)) {
                $languageIds[] = $translation->language_id;

                if ($groupByLanguage) {
                    $gloss2LanguageMap[$translation->language_id] = [];
                }
            }
        }

        // Load the languages and order them by priority. The priority is configured by the Order field in the database.
        $allLanguages = Language::whereIn('id', $languageIds)
            ->orderByPriority()
            ->get();

        // Create a translation to language map which will be used later to associate the translations to their
        // languages. This is a necessary grouping operation due to the sort operation performed later on.
        $sense = [];
        $noOfSense = 0;
        foreach ($translations as $translation) {
            if ($word !== null) {
                self::calculateRating($translation, $word);
            }

            // adapt translation for the view
            $gloss2LanguageMap[$groupByLanguage ? $translation->language_id : 0][] = 
                self::adaptTranslation($translation, $allLanguages, $inflections, $commentsById, $atomDate, $linker);
            
            // Compose an array of senses in an ascending order.
            $senseId = $translation->sense_id;
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

                // Sort the translations based on their previously calculated rating.
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

    private static function adaptTranslation($translation, Collection $languages, array $inflections, array $commentsById, 
        bool $atomDate, LinkHelper $linker) 
    {
        // Filter among the inflections, looking for references to the specified translation.
        // The array is associative two-dimensional with the sentence fragment ID as the key, and an array containing
        // the  inflections associated with the fragment.
        $inflectionsForTranslation = array_filter($inflections, function ($i) use($translation) {
            return $i[0]->translation_id === $translation->id;
        });
        $translation->inflections = count($inflectionsForTranslation) > 0 
            ? $inflectionsForTranslation : null;
        $translation->comment_count = isset($commentsById[$translation->id]) ? $commentsById[$translation->id] : 0;

        // Retrieve language reference to the specified translation
        $translation->language = $languages->first(function ($l) use($translation) {
            return $l->id === $translation->language_id;
        }); // <-- infer success

        // Convert dates
        if (! ($translation->created_at instanceof Carbon)) {
            $date = Carbon::parse($translation->created_at);
            
            if ($atomDate) {
                $translation->created_at = $date->toAtomString();
            } else {
                $translation->created_at = $date;
            }
        }

        // Create links upon the first element of each sentence fragment.
        if ($translation->inflections !== null) {
            foreach ($translation->inflections as $sentenceFragmentId => $inflectionsForFragment) {

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

        return $translation;
    }

    /**
     * Estimates how relevant the specified translation object is based on the search term.
     * @param $translation
     * @param $word
     */
    private static function calculateRating($translation, string $word)
    {
        if (empty($word)) {
            return PHP_INT_MIN;
        }

        $rating = 0;

        // First, check if the gloss contains the search term by looking for its
        // position within the word property, albeit normalized.
        $n = StringHelper::normalize($translation->word);
        $pos = strpos($n, $word);

        if ($pos !== false) {
            // The "cleaner" the match, the better
            $rating = 100000 + ($pos * -1) * 10;

            if ($pos === 0 && $n == $word) {
                $rating *= 2;
            }
        }

        // If the previous check failed, check for the translations field. Statistically,
        // this is the most common case.
        if ($rating === 0) {
            $n = StringHelper::normalize($translation->translation);
            $pos = strpos($n, $word);

            if ($pos !== false) {
                $rating = 10000 + ($pos * -1) * 10;

                if ($pos === 0 && $n == $word) {
                    $rating *= 2;
                }
            }
        }

        // If the previous check failed, check within the comments field. Statistically,
        // this is an uncommon match.
        if ($rating === 0 && $translation->comments !== null) {
            $n = StringHelper::normalize($translation->comments);
            $pos = strpos($n, $word);

            if ($pos !== false) {
                $rating = 1000;
            }
        }

        // Default rating for all other cases, probably matches by keyword.
        if ($rating === 0) {
            $rating = 100;
        }

        // Bump all unverified translations to a trailing position
        if (! $translation->is_canon) {
            $rating = -110000 + $rating;
        }

        $translation->rating = $rating;
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
