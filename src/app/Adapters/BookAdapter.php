<?php

namespace App\Adapters;
use App\Helpers\{
    LinkHelper, StringHelper, MarkdownParser
};
use App\Models\{
    Language, Translation, Account
};
use Illuminate\Support\Collection;

class BookAdapter
{
    /**
     * Transforms the specified translations array to a view model.
     *
     * @param array $translations
     * @param string|null $word
     * @return array
     */
    public function adaptTranslations(array $translations, string $word = null, array $inflections = [])
    {
        $numberOfTranslations = count($translations);

        // * Optimize by dealing with some edge cases first
        //    - No translation results
        if ($numberOfTranslations < 1) {
            return [
                'word' => $word,
                'sections' => []
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

            return self::assignColumnWidths([
                'word' => $word,
                'sections' => [
                    [
                        // Load the language by examining the first (and only) element of the array
                        'language' => Language::findOrFail($translation->language_id),
                        'glosses'  => [ self::adaptTranslation($translation, $inflections, $linker) ]
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
                self::calculateRating($translation, $word);
            }

            // adapt translation for the view
            $translation = self::adaptTranslation($translation, $inflections, $linker);

            if (!isset($gloss2LanguageMap[$translation->language_id])) {
                $gloss2LanguageMap[$translation->language_id] = [ $translation ];
            } else {
                $gloss2LanguageMap[$translation->language_id][] = $translation;
            }

        }

        // Retrieve distinct language IDs which we need to load.
        $languageIds = array_keys($gloss2LanguageMap);

        // Load the languages and order them by priority. The priority is configured by the Order field in the database.
        $allLanguages = Language::whereIn('id', $languageIds)
            ->orderByPriority()
            ->get();

        // Create a section array component for each language in the same order as the languages were retrieved from
        // the database
        $sections = [];
        foreach ($allLanguages as $language) {

            if (!isset($gloss2LanguageMap[$language->id])) {
                continue;
            }

            // Sort the translations based on their previously calculated rating.
            $glosses = $gloss2LanguageMap[$language->id];
            usort($glosses, function ($a, $b) {
                return $a->rating > $b->rating ? -1 : ($a->rating === $b->rating ? 0 : 1);
            });

            $sections[] = [
                'language' => $language,
                'glosses' => $glosses
            ];
        }

        return self::assignColumnWidths([
            'word' => $word,
            'sections' => $sections
        ], count($allLanguages));
    }

    private static function adaptTranslation(\stdClass $translation, array $inflections, LinkHelper $linker) 
    {
        // Filter among the inflections, looking for references to the specified translation.
        // The array is associative two-dimensional with the sentence fragment ID as the key, and an array containing
        // the  inflections associated with the fragment.
        $inflectionsForTranslation = array_filter($inflections, function ($i) use($translation) {
            return $i[0]->translation_id === $translation->id;
        });
        $translation->inflections = count($inflectionsForTranslation) > 0 
            ? $inflectionsForTranslation : null;

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
    private static function calculateRating(\stdClass $translation, string $word)
    {
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
        $min = $numberOfLanguages > 2 ? 4 : $mid;

        $model['columnsMax'] = $max;
        $model['columnsMid'] = $mid;
        $model['columnsMin'] = $min;

        return $model;
    }
}