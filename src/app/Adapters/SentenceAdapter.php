<?php

namespace App\Adapters;

use App\Helpers\LinkHelper;
use App\Models\Language;
use Illuminate\Support\Collection;

class SentenceAdapter
{
    private LinkHelper $_linkHelper;

    public function __construct(LinkHelper $linkHelper)
    {
        $this->_linkHelper = $linkHelper;
    }

    public function adaptSentence(Collection $sentences)
    {
        $sections = [];
        $sectionToLanguageMap = [];

        foreach ($sentences as $sentence) {
            $languageId = $sentence->language_id;
            $language = null;

            if (array_key_exists($languageId, $sectionToLanguageMap)) {
                $section = $sections[$sectionToLanguageMap[$languageId]];
                $language = $section['language'];
                $section['entities'][] = $sentence;
            } else {
                $pos = count($sections);
                $language = Language::findOrFail($languageId);
                $section = [
                    'language' => $language,
                    'entities' => [$sentence],
                ];
                $sections[$pos] = $section;
                $sectionToLanguageMap[$languageId] = $pos;
            }

            $sentence->link_href = $this->_linkHelper->sentence($language->id, $language->name, $sentence->id, $sentence->name);
        }

        return [
            'sections' => $sections,
        ];
    }
}
