<?php

namespace App\Jobs;

use App\Adapters\BookAdapter;
use App\Helpers\LinkHelper;
use App\Models\LexicalEntryDerivation;
use App\Models\LexicalEntryDerivationData;
use App\Models\LexicalEntryPhoneticDevelopment;
use App\Repositories\LexicalEntryDerivationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Precomputes lexical_entry_derivation_data (own ancestor chain, own phonetic-development chain,
 * and descendant "Derivatives" tree) for every entry that could ever have any of the three. This
 * data only changes on import, so it's computed once here rather than live per request — see
 * BookAdapter::adaptLexicalEntry() and LexicalEntryRepository, which just read the precomputed
 * result back.
 *
 * Dispatched at the end of the Eldamo import (ImportEldamoCommand), and available on demand via
 * `php artisan ed-import:rebuild-derivation-data`.
 */
class RebuildLexicalEntryDerivationData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Chunk size for the batched queries below — bounds each batch's eager-loaded-relation
     * footprint, the same technique that was missing from the original live request-time path.
     */
    private const CHUNK_SIZE = 50;

    /**
     * @param  array<int>|null  $onlyLexicalEntryIds  Restrict the rebuild to these entries instead of
     *                                                every candidate in the database. Production callers
     *                                                (the import command, the console command) always
     *                                                pass null for a full rebuild; tests pass the small
     *                                                set of entries they created so they aren't forced to
     *                                                reprocess the entire dataset on every run.
     */
    public function handle(LexicalEntryDerivationRepository $derivationRepository, BookAdapter $bookAdapter, LinkHelper $linkHelper, ?array $onlyLexicalEntryIds = null): void
    {
        $candidateIds = LexicalEntryDerivation::query()->distinct()->pluck('lexical_entry_id')
            ->merge(LexicalEntryPhoneticDevelopment::query()->distinct()->pluck('lexical_entry_id'))
            ->merge(LexicalEntryDerivation::query()->whereNotNull('parent_lexical_entry_id')->distinct()->pluck('parent_lexical_entry_id'))
            ->unique()
            ->values();

        if ($onlyLexicalEntryIds !== null) {
            $candidateIds = $candidateIds->intersect($onlyLexicalEntryIds)->values();
        }

        // Full rebuild: entries that no longer qualify (e.g. their derivation data was removed by
        // a later import) shouldn't keep a stale row around. A plain DELETE, not truncate() — on
        // MySQL, TRUNCATE is DDL and implicitly commits the current transaction, which would
        // silently break any caller running this inside one (e.g. the backfill migration, or a
        // test wrapped in DatabaseTransactions).
        //
        // A scoped rebuild must not wipe unrelated entries' precomputed data, so it only clears
        // rows for the entries actually being rebuilt.
        if ($onlyLexicalEntryIds === null) {
            LexicalEntryDerivationData::query()->delete();
        } else {
            LexicalEntryDerivationData::query()->whereIn('lexical_entry_id', $candidateIds)->delete();
        }

        $candidateIds->chunk(self::CHUNK_SIZE)->each(function ($idsChunk) use ($derivationRepository, $bookAdapter, $linkHelper) {
            $descendantRows = $derivationRepository->getDescendantTreesForLexicalEntries($idsChunk->all());

            $ownDerivationsByEntryId = LexicalEntryDerivation::whereIn('lexical_entry_id', $idsChunk)
                ->with('parent_lexical_entry.word', 'parent_lexical_entry.glosses', 'parent_lexical_entry.speech')
                ->get()
                ->groupBy('lexical_entry_id');

            $ownPhoneticDevelopmentsByEntryId = LexicalEntryPhoneticDevelopment::whereIn('lexical_entry_id', $idsChunk)
                ->get()
                ->groupBy('lexical_entry_id');

            $now = now();
            $rows = $idsChunk->map(function ($id) use ($bookAdapter, $linkHelper, $descendantRows, $ownDerivationsByEntryId, $ownPhoneticDevelopmentsByEntryId, $now) {
                $data = $bookAdapter->adaptDerivationData(
                    $ownDerivationsByEntryId->get($id, collect()),
                    $ownPhoneticDevelopmentsByEntryId->get($id, collect()),
                    $descendantRows,
                    $id,
                    $linkHelper,
                );

                return [
                    'lexical_entry_id' => $id,
                    'derivations' => json_encode($data['derivations']),
                    'derivatives' => json_encode($data['derivatives']),
                    'phonetic_developments' => json_encode($data['phonetic_developments']),
                    'updated_at' => $now,
                ];
            })->all();

            LexicalEntryDerivationData::upsert($rows, ['lexical_entry_id'], ['derivations', 'derivatives', 'phonetic_developments', 'updated_at']);
        });

        Log::info(sprintf('RebuildLexicalEntryDerivationData: rebuilt %d entries.', $candidateIds->count()));
    }
}
