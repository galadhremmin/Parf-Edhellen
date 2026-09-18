<?php

namespace App\Services;

use App\Helpers\SentenceBuilders\SentenceBuilder;
use App\Helpers\StringHelper;
use App\Models\LexicalEntry;
use App\Models\Sentence;
use App\Services\Enumerations\SearchTermKind;
use App\Services\ValueObjects\SearchIndexAuditTotals;
use Illuminate\Support\Collection;

/**
 * Read-only comparison of what the search index holds against what the indexer would write today.
 * Shared by the audit and reindex commands so both agree on what counts as a gap.
 */
class SearchIndexAuditor
{
    private const CHUNK_SIZE = 2000;

    /**
     * Calls back with every active entry that has an index but is missing terms, as the entry's ID and a
     * collection of missing terms keyed by term. Entries with no index at all are counted, not reported:
     * they need `entriesWithoutIndex` instead.
     *
     * @param  int[]  $ids  limits the audit to these entries; empty audits the whole glossary
     */
    public function eachEntryWithMissingTerms(callable $callback, array $ids = []): SearchIndexAuditTotals
    {
        $checked = 0;
        $unindexed = 0;

        $entries = LexicalEntry::active()
            ->when($ids, fn ($query) => $query->whereIn('id', $ids))
            ->select('id')
            ->with([
                'glosses:lexical_entry_id,translation',
                'keywords:lexical_entry_id,keyword,sentence_fragment_id',
                'lexical_entry_inflections:lexical_entry_id,word,sentence_fragment_id',
                'search_keywords:entity_name,entity_id,keyword',
            ])
            ->lazyById(self::CHUNK_SIZE);

        foreach ($entries as $lexicalEntry) {
            if ($lexicalEntry->search_keywords->isEmpty()) {
                $unindexed++;

                continue;
            }

            $checked++;
            $indexed = $lexicalEntry->search_keywords->pluck('keyword')->flip();
            $missing = $this->expectedTerms($lexicalEntry)
                ->reject(fn (SearchTermKind $kind, string $term) => $indexed->has($term));

            if ($missing->isNotEmpty()) {
                $callback($lexicalEntry->id, $missing);
            }
        }

        return new SearchIndexAuditTotals($checked, $unindexed);
    }

    /**
     * @return Collection<int, int> the IDs of active entries with no search index at all
     */
    public function entriesWithoutIndex(): Collection
    {
        return LexicalEntry::active()
            ->whereDoesntHave('search_keywords')
            ->orderBy('id')
            ->pluck('id');
    }

    /**
     * @return Collection<int, int> the IDs of phrases whose index is missing words, or whose rows predate the
     *                              language being recorded -- searches filter on language_id, so those rows
     *                              are unreachable
     */
    public function phrasesNeedingReindex(): Collection
    {
        return Sentence::where(function ($sentence) {
            $sentence->whereHas('sentence_fragments', function ($fragment) {
                $fragment->where('type', SentenceBuilder::TYPE_CODE_WORD)
                    ->whereDoesntHave('search_keywords');
            })->orWhereHas('sentence_fragments.search_keywords', function ($index) {
                $index->whereNull('language_id');
            });
        })
            ->orderBy('id')
            ->pluck('id');
    }

    /**
     * The terms the indexer would write for one entry, keyed by term. A term can arise more than once --
     * as the entry's own keyword and again through a phrase -- and `union` keeps the first kind seen, so
     * the collections are ordered by how plainly each describes the term.
     *
     * @return Collection<string, SearchTermKind>
     */
    private function expectedTerms(LexicalEntry $lexicalEntry): Collection
    {
        $glosses = $lexicalEntry->glosses->pluck('translation');

        [$keywords, $phraseKeywords] = $lexicalEntry->keywords
            ->reject(fn ($keyword) => $glosses->contains($keyword->keyword))
            ->partition(fn ($keyword) => $keyword->sentence_fragment_id === null);

        [$inflections, $phraseInflections] = $lexicalEntry->lexical_entry_inflections
            ->partition(fn ($inflection) => $inflection->sentence_fragment_id === null);

        return $this->termsOfKind($keywords->pluck('keyword'), SearchTermKind::KEYWORD)
            ->union($this->termsOfKind($glosses, SearchTermKind::GLOSS))
            ->union($this->termsOfKind($inflections->pluck('word'), SearchTermKind::INFLECTION))
            ->union($this->termsOfKind($phraseKeywords->pluck('keyword'), SearchTermKind::PHRASE_KEYWORD))
            ->union($this->termsOfKind($phraseInflections->pluck('word'), SearchTermKind::PHRASE_INFLECTION));
    }

    /**
     * @param  Collection<int, string>  $terms
     * @return Collection<string, SearchTermKind> the terms keyed by themselves, each mapped to `$kind`
     */
    private function termsOfKind(Collection $terms, SearchTermKind $kind): Collection
    {
        return $terms->map(fn (string $term) => $this->normalise($term))
            ->reject(fn (?string $term) => $term === null || $term === '')
            ->mapWithKeys(fn (string $term) => [$term => $kind]);
    }

    /**
     * Matches how SearchIndexRepository stores a keyword. Comparison is byte-exact because the columns'
     * collations treat 'la' and 'lá' as equal.
     */
    private function normalise(?string $value): ?string
    {
        return StringHelper::toLower(StringHelper::clean($value));
    }
}
