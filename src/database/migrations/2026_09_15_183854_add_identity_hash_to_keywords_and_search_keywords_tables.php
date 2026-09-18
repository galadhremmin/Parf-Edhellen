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

    private const DELETE_BATCH_SIZE = 10000;

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

            if (! Schema::hasColumn($table, 'identity_hash')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->char('identity_hash', 32)->nullable()->index();
                });
            }

            $this->backfill($model, $table);
            $this->deleteDuplicates($table);

            // Each step is conditional so the migration can be re-run after an interruption.
            $indexes = collect(Schema::getIndexes($table))->keyBy('name');

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
        DB::table($table)
            ->whereNull('identity_hash')
            ->select(array_merge(['id'], $model::identityColumns()))
            ->chunkById(self::CHUNK_SIZE, function ($rows) use ($model, $table) {
                $cases = '';
                $bindings = [];

                foreach ($rows as $row) {
                    $cases .= ' WHEN '.intval($row->id).' THEN ?';
                    $bindings[] = $model::identityHash((array) $row);
                }

                $ids = $rows->pluck('id')->map(fn ($id) => intval($id))->implode(',');
                DB::update("UPDATE {$table} SET identity_hash = CASE id{$cases} END WHERE id IN ({$ids})", $bindings);
            });
    }

    private function deleteDuplicates(string $table): void
    {
        // A batch at a time: the tables hold hundreds of thousands of duplicate groups, and deleting a group's
        // extra rows takes it out of the next batch.
        do {
            $duplicates = DB::table($table)
                ->select('identity_hash', DB::raw('MAX(id) AS keep_id'))
                ->groupBy('identity_hash')
                ->havingRaw('COUNT(*) > 1')
                ->limit(self::DELETE_BATCH_SIZE)
                ->get();

            if ($duplicates->isNotEmpty()) {
                DB::table($table)
                    ->whereIn('identity_hash', $duplicates->pluck('identity_hash'))
                    ->whereNotIn('id', $duplicates->pluck('keep_id'))
                    ->delete();
            }
        } while ($duplicates->isNotEmpty());
    }
};
