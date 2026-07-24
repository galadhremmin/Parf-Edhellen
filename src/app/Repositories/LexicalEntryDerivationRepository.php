<?php

namespace App\Repositories;

use App\Models\LexicalEntry;
use App\Models\LexicalEntryDerivation;
use App\Models\LexicalEntryPhoneticDevelopment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LexicalEntryDerivationRepository
{
    /**
     * All ancestry hypotheses for the specified entry, grouped by their derivation group UUID.
     * Each group is one hypothesis, ordered from the immediate parent towards the root.
     */
    public function getDerivationsForLexicalEntry(int $lexicalEntryId): Collection
    {
        return LexicalEntryDerivation::where('lexical_entry_id', $lexicalEntryId)
            ->with('parent_lexical_entry', 'parent_language')
            ->orderBy('order')
            ->get()
            ->groupBy('derivation_group_uuid');
    }

    /**
     * All derivation rows naming the specified entry as an ancestor — the raw material
     * for the "words derived from this root" (node) view.
     */
    public function getDerivationsFromLexicalEntry(int $parentLexicalEntryId): Collection
    {
        return LexicalEntryDerivation::where('parent_lexical_entry_id', $parentLexicalEntryId)
            ->with('lexical_entry')
            ->get();
    }

    /**
     * Raw material for the "words derived from this root" (Derivatives) tree: every row of
     * every hypothesis that names the given entry as an ancestor at some point in its chain,
     * grouped by hypothesis (derivation_group_uuid) so each descendant's full path from the
     * root down can be walked (see `BookAdapter::adaptDerivatives()`). Two bounded, indexed
     * queries — `parent_lexical_entry_id` is indexed, so this never scans the table.
     */
    public function getDescendantTree(int $rootLexicalEntryId): Collection
    {
        return $this->getDescendantTreesForLexicalEntries([$rootLexicalEntryId]);
    }

    /**
     * Batched version of getDescendantTree() for several roots at once — used when loading a
     * page of entries so the cost stays two indexed queries total (not two per entry). Returns
     * one shared collection of grouped rows spanning every root in $rootLexicalEntryIds;
     * `BookAdapter::adaptDerivatives()` filters it down to whichever hypotheses actually name a
     * given root as an ancestor when adapting that entry.
     */
    public function getDescendantTreesForLexicalEntries($rootLexicalEntryIds): Collection
    {
        $ids = collect($rootLexicalEntryIds)->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $hitGroupUuids = LexicalEntryDerivation::whereIn('parent_lexical_entry_id', $ids)
            ->pluck('derivation_group_uuid')
            ->unique();

        if ($hitGroupUuids->isEmpty()) {
            return collect();
        }

        return LexicalEntryDerivation::whereIn('derivation_group_uuid', $hitGroupUuids)
            ->with('lexical_entry.word', 'lexical_entry.glosses', 'parent_lexical_entry.word', 'parent_lexical_entry.glosses')
            ->orderBy('order')
            ->get()
            ->groupBy('derivation_group_uuid');
    }

    public function getPhoneticDevelopmentsForLexicalEntry(int $lexicalEntryId): Collection
    {
        return LexicalEntry::findOrFail($lexicalEntryId)
            ->lexical_entry_phonetic_developments()
            ->orderBy('order')
            ->get()
            ->groupBy('derivation_group_uuid');
    }

    /**
     * Replaces the entry's derivations and phonetic developments wholesale. Unlike inflections,
     * an empty collection still clears existing rows: an entry can legitimately lose its
     * derivations between imports.
     *
     * The delete/insert is skipped entirely when the incoming data is identical to what's
     * already stored (compared by content, not by derivation_group_uuid — a fresh import
     * assigns new UUIDs to unchanged hypotheses every run). During a full import most entries
     * are re-imports of unchanged Eldamo data, so this avoids the write for the common case.
     */
    public function saveManyOnLexicalEntry(LexicalEntry $lexicalEntry, Collection $derivations, Collection $phoneticDevelopments): void
    {
        $existingDerivations = $lexicalEntry->lexical_entry_derivations()->orderBy('order')->get();
        $existingPhoneticDevelopments = $lexicalEntry->lexical_entry_phonetic_developments()->orderBy('order')->get();

        if ($this->derivationSignature($existingDerivations) === $this->derivationSignature($derivations)
            && $this->phoneticDevelopmentSignature($existingPhoneticDevelopments) === $this->phoneticDevelopmentSignature($phoneticDevelopments)) {
            return;
        }

        DB::transaction(function () use ($lexicalEntry, $derivations, $phoneticDevelopments) {
            $lexicalEntry->lexical_entry_derivations()->delete();
            $lexicalEntry->lexical_entry_phonetic_developments()->delete();

            if ($derivations->count() > 0) {
                $lexicalEntry->lexical_entry_derivations()->saveMany($derivations);
            }

            if ($phoneticDevelopments->count() > 0) {
                $lexicalEntry->lexical_entry_phonetic_developments()->saveMany($phoneticDevelopments);
            }
        });
    }

    /**
     * Content signature of a set of derivation hypotheses, order-independent across hypotheses
     * (since derivation_group_uuid is regenerated every import) but order-sensitive within a
     * hypothesis's chain. Excludes parent_lexical_entry_id, which is populated by a later,
     * separate resolution pass and shouldn't itself count as a content change.
     *
     * Signature fields are derived from the model's $fillable list (minus the excluded/grouping
     * columns) rather than hardcoded, so new columns are picked up automatically instead of
     * silently being left out of change detection. Values are read via getAttribute() rather
     * than toArray(), since toArray() only includes attributes that were actually set — an
     * unsaved model built without e.g. 'comment' would otherwise omit the key entirely instead
     * of reporting it as null, making it non-comparable with a persisted row that always has it.
     */
    private function derivationSignature(Collection $derivations): array
    {
        $keys = array_diff((new LexicalEntryDerivation)->getFillable(), [
            'derivation_group_uuid', 'lexical_entry_id', 'parent_lexical_entry_id',
        ]);

        return $this->signature($derivations, $keys, [
            'is_uncertain' => 'bool',
            'is_rejected' => 'bool',
        ]);
    }

    /**
     * Content signature of a set of phonetic development chains, order-independent across
     * groups but order-sensitive within a group. See derivationSignature() for the rationale,
     * including why the field list is derived from the model rather than hardcoded.
     */
    private function phoneticDevelopmentSignature(Collection $phoneticDevelopments): array
    {
        $keys = array_diff((new LexicalEntryPhoneticDevelopment)->getFillable(), [
            'derivation_group_uuid', 'lexical_entry_id',
        ]);

        return $this->signature($phoneticDevelopments, $keys);
    }

    /**
     * Groups $rows by derivation_group_uuid, and within each group reduces every row to the
     * given $keys (read via getAttribute() for null-safety) ordered by 'order'. Fields listed
     * in $boolCasts are normalised to bool, since is_uncertain/is_rejected come back as 0/1
     * from the DB but true/false on a freshly-built model. Groups are sorted and encoded so the
     * result compares equal regardless of derivation_group_uuid or group ordering.
     */
    private function signature(Collection $rows, array $keys, array $boolCasts = []): array
    {
        return $rows
            ->groupBy('derivation_group_uuid')
            ->map(fn (Collection $group) => $group->sortBy('order')->map(function ($row) use ($keys, $boolCasts) {
                $values = [];
                foreach ($keys as $key) {
                    $value = $row->getAttribute($key);
                    $values[$key] = isset($boolCasts[$key]) ? (bool) $value : $value;
                }

                return $values;
            })->values()->toArray())
            ->map(fn (array $group) => json_encode($group))
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Second import pass: links derivation rows to their parent entries by external ID.
     * Optionally constrained to entries within the specified groups, to avoid matching
     * external IDs from unrelated imports.
     *
     * @return int the number of rows resolved
     */
    public function resolveParentReferences(?array $lexicalEntryGroupIds = null): int
    {
        $query = DB::table('lexical_entry_derivations AS d')
            ->join('lexical_entries AS parent', function ($join) {
                $join->on('parent.external_id', '=', 'd.parent_external_id')
                    ->where('parent.is_deleted', 0);
            })
            ->whereNull('d.parent_lexical_entry_id')
            ->whereNotNull('d.parent_external_id');

        if ($lexicalEntryGroupIds !== null) {
            $query->whereIn('parent.lexical_entry_group_id', $lexicalEntryGroupIds);
        }

        return $query->update(['d.parent_lexical_entry_id' => DB::raw('parent.id')]);
    }
}
