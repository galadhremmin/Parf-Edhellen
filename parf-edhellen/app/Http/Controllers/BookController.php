<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\StringHelper;
use App\Models\Language;
use App\Repositories\TranslationRepository;

class BookController extends Controller
{
    private $_translationRepository;

    public function __construct(TranslationRepository $translationRepository)
    {
        $this->_translationRepository = $translationRepository;
    }

    public function pageForWord(Request $request, $word)
    {
        $translations = $this->_translationRepository->getWordTranslations($word);
        $model = $this->adapt($translations->toArray(), $word);

        return view($request->ajax() ? 'book._page' : 'book.page', $model);
    }

    public function pageForTranslationId(Request $request, $id)
    {
        $translation = $this->_translationRepository->getTranslation($id);
        $model = $this->adapt([ $translation ]);

        return view($request->ajax() ? 'book._page' : 'book.page', $model);
    }

    /**
     * Transforms the specified translations array to a view model.
     *
     * @param array $translations
     * @param string|null $word
     * @return array
     */
    private function adapt(array $translations, string $word = null) {
        $numberOfTranslations = count($translations) ;

        // * Optimize by dealing with some edge cases first
        //    - No translation results
        if ($numberOfTranslations < 1) {
            return [ 'sections' => [] ];
        }

        //    - Just one translation result.
        if ($numberOfTranslations === 1) {
            return $this->assignColumnWidths([
                'sections' => [
                    [
                        // Load the language by examining the first (and only) element of the array
                        'language' => Language::findOrFail($translations[0]->LanguageID),
                        'glosses' => $translations
                    ]
                ]
            ], 1);
        }

        // * Multiple translations (possibly across multiple languages)

        // Create a translation to language map which will be used later to associate the translations to their
        // languages. This is a necessary grouping operation due to the sort operation performed later on.
        $gloss2LanguageMap = [];
        foreach ($translations as $translation) {
            if ($word !== null) {
                $this->calculateRating($translation, $word);
            }

            if (!isset($gloss2LanguageMap[$translation->LanguageID])) {
                $gloss2LanguageMap[$translation->LanguageID] = [ $translation ];
            } else {
                $gloss2LanguageMap[$translation->LanguageID][] = $translation;
            }

        }

        // Retrieve distinct language IDs which we need to load.
        $languageIds = array_keys($gloss2LanguageMap);

        // Load the languages and order them by priority. The priority is configured by the Order field in the database.
        $allLanguages = Language::whereIn('ID', $languageIds)
            ->orderByPriority()
            ->get();

        // Create a section array component for each language in the same order as the languages were retrieved from
        // the database
        $sections = [];
        foreach ($allLanguages as $language) {

            if (!isset($gloss2LanguageMap[$language->ID])) {
                continue;
            }

            // Sort the translations based on their previously calculated rating.
            $glosses = $gloss2LanguageMap[$language->ID];
            usort($glosses, function ($a, $b) {
                return $a->Rating > $b->Rating ? -1 : ($a->Rating === $b->Rating ? 0 : 1);
            });

            $sections[] = [
                'language' => $language,
                'glosses' => $glosses
            ];
        }

        return $this->assignColumnWidths([ 'sections' => $sections ], count($allLanguages));
    }

    /**
     * Estimates how relevant the specified translation object is based on the search term.
     * @param $translation
     * @param $term
     */
    private static function calculateRating($translation, $term) {
        $rating = 0;

        // First, check if the gloss contains the search term by looking for its
        // position within the word property, albeit normalized.
        $n = StringHelper::normalize($translation->Word);
        $pos = strpos($n, $term);

        if ($pos !== false) {
            // The "cleaner" the match, the better
            $rating = 100000 + ($pos * -1) * 10;

            if ($pos === 0 && $n == $term) {
                $rating *= 2;
            }
        }

        // If the previous check failed, check for the translations field. Statistically,
        // this is the most common case.
        if ($rating === 0) {
            $n = StringHelper::normalize($translation->Translation);
            $pos = strpos($n, $term);

            if ($pos !== false) {
                $rating = 10000 + ($pos * -1) * 10;

                if ($pos === 0 && $n == $term) {
                    $rating *= 2;
                }
            }
        }

        // If the previous check failed, check within the comments field. Statistically,
        // this is an uncommon match.
        if ($rating === 0 && $translation->Comments !== null) {
            $n = StringHelper::normalize($translation->Comments);
            $pos = strpos($n, $term);

            if ($pos !== false) {
                $rating = 1000;
            }
        }

        // Default rating for all other cases, probably matches by keyword.
        if ($rating === 0) {
            $rating = 100;
        }

        // Bump all unverified translations to a trailing position
        if (! $translation->Canon) {
            $rating = -110000 + $rating;
        }

        $translation->Rating = $rating;
    }


    private function assignColumnWidths(array $model, $numberOfLanguages) {
        $max = 12;
        $mid = $numberOfLanguages > 1 ? 6 : $max;
        $min = $numberOfLanguages > 2 ? 4 : $mid;

        $model['columnsMax'] = $max;
        $model['columnsMid'] = $mid;
        $model['columnsMin'] = $min;

        return $model;
    }
}

