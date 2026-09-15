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

            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['identity_hash']);
                $table->char('identity_hash', 32)->nullable(false)->change();
                $table->unique('identity_hash');
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
        $duplicates = DB::table($table)
            ->select('identity_hash', DB::raw('MAX(id) AS keep_id'))
            ->groupBy('identity_hash')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates->chunk(self::CHUNK_SIZE) as $chunk) {
            DB::table($table)
                ->whereIn('identity_hash', $chunk->pluck('identity_hash'))
                ->whereNotIn('id', $chunk->pluck('keep_id'))
                ->delete();
        }
    }
};
