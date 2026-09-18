<?php

use App\Models\Keyword;
use App\Models\SearchKeyword;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CHUNK_SIZE = 1000;

    private const DELETE_BATCH_SIZE = 2000;

    // Production is a small burstable instance: pausing between batches keeps the site responsive and the
    // CPU nearer its sustainable baseline, at the cost of a slower migration.
    private const PAUSE_BETWEEN_BATCHES_MS = 100;

    private const REPORT_EVERY_BATCHES = 10;

    private const MODELS = [Keyword::class, SearchKeyword::class];

    /**
     * Gives both tables an identity_hash unique key so their repositories' upserts update rather than insert
     * duplicates. Existing duplicates are removed first, keeping the newest row of each group, as that's the
     * one an upsert would have updated.
     */
    public function up(): void
    {
        foreach (self::MODELS as $model) {
            $table = (new $model)->getTable();

            // Each step is conditional so the migration can be re-run after an interruption.
            if (! Schema::hasColumn($table, 'identity_hash')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->char('identity_hash', 32)->nullable();
                });
            }

            // The index is built after the backfill, so the backfill's updates don't have to maintain it.
            $this->backfill($model, $table);

            $indexes = collect(Schema::getIndexes($table))->keyBy('name');

            if (! $indexes->has($table.'_identity_hash_index') && ! $indexes->has($table.'_identity_hash_unique')) {
                $this->report($table, 'building the identity_hash index');
                Schema::table($table, function (Blueprint $table) {
                    $table->index('identity_hash');
                });
                $indexes = collect(Schema::getIndexes($table))->keyBy('name');
            }

            $this->deleteDuplicates($table);

            $this->report($table, 'adding the unique key');

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
                if ($indexes->has($table.'_identity_hash_index')) {
                    $blueprint->dropIndex(['identity_hash']);
                }

                $blueprint->char('identity_hash', 32)->nullable(false)->change();

                if (! $indexes->has($table.'_identity_hash_unique')) {
                    $blueprint->unique('identity_hash');
                }
            });
        }
    }

    /**
     * Reverse the migrations. Deleted duplicates are not restored.
     */
    public function down(): void
    {
        foreach (self::MODELS as $model) {
            Schema::table((new $model)->getTable(), function (Blueprint $table) {
                $table->dropUnique(['identity_hash']);
                $table->dropColumn('identity_hash');
            });
        }
    }

    private function backfill(string $model, string $table): void
    {
        $remaining = DB::table($table)->whereNull('identity_hash')->count();
        $done = 0;
        $batches = 0;
        $started = microtime(true);
        $this->report($table, sprintf('hashing %d rows', $remaining));

        DB::table($table)
            ->whereNull('identity_hash')
            ->select(array_merge(['id'], $model::identityColumns()))
            ->chunkById(self::CHUNK_SIZE, function ($rows) use ($model, $table, $remaining, &$done, &$batches, &$started) {
                $cases = '';
                $bindings = [];

                foreach ($rows as $row) {
                    $cases .= ' WHEN '.intval($row->id).' THEN ?';
                    $bindings[] = $model::identityHash((array) $row);
                }

                $ids = $rows->pluck('id')->map(fn ($id) => intval($id))->implode(',');
                DB::update("UPDATE {$table} SET identity_hash = CASE id{$cases} END WHERE id IN ({$ids})", $bindings);

                $done += $rows->count();
                $batches++;

                // Every few batches, so a slowdown shows up within a minute on production.
                if ($batches % self::REPORT_EVERY_BATCHES === 0) {
                    $perBatch = (microtime(true) - $started) / self::REPORT_EVERY_BATCHES;
                    $this->report($table, sprintf('hashed %d of %d rows (%.2fs per batch, pauses excluded)', $done, $remaining,
                        $perBatch - self::PAUSE_BETWEEN_BATCHES_MS / 1000));
                    $started = microtime(true);
                }

                $this->pause();
            });
    }

    private function deleteDuplicates(string $table): void
    {
        $deleted = 0;
        $this->report($table, 'deleting duplicates');

        // A batch at a time, paging through the index by hash, so each batch reads on from where the last
        // stopped rather than scanning the whole table again.
        $after = '';

        do {
            $duplicates = DB::table($table)
                ->select('identity_hash', DB::raw('MAX(id) AS keep_id'))
                ->where('identity_hash', '>', $after)
                ->groupBy('identity_hash')
                ->havingRaw('COUNT(*) > 1')
                ->orderBy('identity_hash')
                ->limit(self::DELETE_BATCH_SIZE)
                ->get();

            if ($duplicates->isNotEmpty()) {
                $deleted += DB::table($table)
                    ->whereIn('identity_hash', $duplicates->pluck('identity_hash'))
                    ->whereNotIn('id', $duplicates->pluck('keep_id'))
                    ->delete();

                $after = $duplicates->last()->identity_hash;
                $this->report($table, sprintf('deleted %d duplicates so far', $deleted));
                $this->pause();
            }
        } while ($duplicates->isNotEmpty());
    }

    private function pause(): void
    {
        usleep(self::PAUSE_BETWEEN_BATCHES_MS * 1000);
    }

    /**
     * Migrations have no console output of their own; this makes a long run on production visibly alive.
     */
    private function report(string $table, string $message): void
    {
        echo sprintf('  [%s] %s: %s', now()->format('H:i:s'), $table, $message).PHP_EOL;
    }
};
