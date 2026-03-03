<?php

namespace App\Repositories;

use App\Models\SearchDefinition;
use App\Models\SearchViewEvent;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use Carbon\Carbon;

class SearchDefinitionRepository
{
    /**
     * Builds a canonical array from SearchIndexSearchValue for stable JSON serialization.
     * Uses JSON with keys in fixed order; excludes deprecated fields (reversed, natural_language).
     */
    public function getIdForSearchValue(SearchIndexSearchValue $v): string
    {
        $data = $this->toCanonicalArray($v);

        return md5(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Records a search view: ensures the search definition exists and appends a view event.
     */
    public function recordView(SearchIndexSearchValue $v, string $searchTerm): void
    {
        $searchTerm = trim(substr($searchTerm, 0, 128));
        if ($searchTerm === '') {
            return;
        }

        $id = $this->getIdForSearchValue($v);
        $display = $this->toDisplayAttributes($v, $searchTerm);

        SearchDefinition::firstOrCreate(
            ['id' => $id],
            $display
        );

        SearchViewEvent::create([
            'search_id' => $id,
            'viewed_at' => Carbon::now(),
        ]);
    }

    /**
     * Builds the canonical array for JSON serialization. Keys in fixed order.
     */
    private function toCanonicalArray(SearchIndexSearchValue $v): array
    {
        $groupIds = $v->getLexicalEntryGroupIds();
        $speechIds = $v->getSpeechIds();

        $groupIdsArr = [];
        if (is_array($groupIds) && count($groupIds) > 0) {
            $groupIdsArr = array_map('intval', $groupIds);
            sort($groupIdsArr);
        }

        $speechIdsArr = [];
        if (is_array($speechIds) && count($speechIds) > 0) {
            $speechIdsArr = array_map('intval', $speechIds);
            sort($speechIdsArr);
        }

        return [
            'word' => (string) ($v->getWord() ?? ''),
            'language_id' => (int) $v->getLanguageId(),
            'lexical_entry_group_ids' => $groupIdsArr,
            'speech_ids' => $speechIdsArr,
            'include_old' => $v->getIncludesOld(),
            'inflections' => $v->getIncludesInflections(),
        ];
    }

    /**
     * Builds attributes for the search_definitions table (display/storage).
     */
    private function toDisplayAttributes(SearchIndexSearchValue $v, string $searchTerm): array
    {
        $groupIds = $v->getLexicalEntryGroupIds();
        $speechIds = $v->getSpeechIds();
        $languageId = $v->getLanguageId();

        $speechIdsStr = null;
        if (is_array($speechIds) && count($speechIds) > 0) {
            $ids = array_map('intval', $speechIds);
            sort($ids);
            $speechIdsStr = implode(',', $ids);
        }

        $lexicalEntryGroupIdsStr = null;
        if (is_array($groupIds) && count($groupIds) > 0) {
            $ids = array_map('intval', $groupIds);
            sort($ids);
            $lexicalEntryGroupIdsStr = implode(',', $ids);
        }

        return [
            'search_term' => $searchTerm,
            'language_id' => $languageId > 0 ? $languageId : null,
            'speech_ids' => $speechIdsStr,
            'lexical_entry_group_ids' => $lexicalEntryGroupIdsStr,
        ];
    }
}
