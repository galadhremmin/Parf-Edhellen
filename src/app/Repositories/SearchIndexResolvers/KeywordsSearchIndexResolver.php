<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Helpers\StringHelper;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class KeywordsSearchIndexResolver extends SearchIndexResolverBase
{
    protected function resolveByQuery(array $params, SearchIndexSearchValue $value): array
    {
        $searchColumn = $params['search_column'];

        try {
            $keywords = $params['query'] //
                ->select(
                    'search_group as g',
                    'keyword as k',
                    'normalized_keyword as nk',
                    'word as ok'
                )
                ->groupBy(
                    'search_group',
                    'keyword',
                    'normalized_keyword',
                    'word'
                )
                ->orderBy('search_group', 'asc')
                ->orderBy(DB::raw('MAX('.$params['length_column'].')'), 'asc')
                ->orderBy($searchColumn, 'asc')
                ->limit(100)
                ->get()
                ->toArray();
        } catch (QueryException $_) {
            $keywords = [];
        }

        return $this->rankKeywords($keywords, $value);
    }

    /**
     * Re-orders the (already length/alphabetically sorted) results within each search group so that
     * an exact match for the searched word comes first, and keywords prefixed with a symbol (e.g.
     * reconstructed/uncertain forms marked with '*') sort last. Relies on a stable sort to preserve
     * the SQL-provided ordering as the tie-breaker.
     */
    private function rankKeywords(array $keywords, SearchIndexSearchValue $value): array
    {
        $exactMatch = StringHelper::transliterate($value->getWord(), false);

        usort($keywords, function (array $a, array $b) use ($exactMatch) {
            if ($a['g'] !== $b['g']) {
                return $a['g'] <=> $b['g'];
            }

            $aIsExact = $a['nk'] === $exactMatch;
            $bIsExact = $b['nk'] === $exactMatch;
            if ($aIsExact !== $bIsExact) {
                return $aIsExact ? -1 : 1;
            }

            $aHasSymbolPrefix = $this->hasSymbolPrefix($a['nk']);
            $bHasSymbolPrefix = $this->hasSymbolPrefix($b['nk']);
            if ($aHasSymbolPrefix !== $bHasSymbolPrefix) {
                return $aHasSymbolPrefix ? 1 : -1;
            }

            return 0;
        });

        return $keywords;
    }

    private function hasSymbolPrefix(string $keyword): bool
    {
        return isset($keyword[0]) && ! ctype_alnum($keyword[0]);
    }
}
