<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SearchKeyword;
use App\Helpers\StringHelper;

return new class extends Migration
{
    private const CHUNK_SIZE = 1000;

    /**
     * Run the migrations.
     */
    public function up(): void
    {        
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->dropFullText(['normalized_keyword_reversed']);
            $table->dropFullText(['normalized_keyword_reversed_unaccented']);

            $table->dropColumn('normalized_keyword_reversed');
            $table->dropColumn('normalized_keyword_reversed_unaccented');
            $table->dropColumn('normalized_keyword_reversed_length');
            $table->dropColumn('normalized_keyword_reversed_unaccented_length');
        });

        // Process in chunks for better performance with large datasets
        // Using chunkById is more efficient than chunk as it uses primary key offsets
        $tableName = (new SearchKeyword())->getTable();
        
        SearchKeyword::chunkById(self::CHUNK_SIZE, function ($keywords) use ($tableName) {
            $updates = [];
            
            foreach ($keywords as $keyword) {
                $normalizedKeyword = StringHelper::transliterate($keyword->keyword, /* transformAccentsIntoLetters = */ true, /* longAccents = */ true);
                $normalizedKeywordUnaccented = StringHelper::transliterate($keyword->keyword, /* transformAccentsIntoLetters = */ false);
                
                $updates[] = [
                    'id' => $keyword->id,
                    'normalized_keyword' => $normalizedKeyword,
                    'normalized_keyword_unaccented' => $normalizedKeywordUnaccented,
                ];
            }
            
            // Bulk update using raw SQL with CASE statements for maximum performance
            if (!empty($updates)) {
                $ids = array_column($updates, 'id');
                $normalizedKeywordCases = '';
                $normalizedKeywordUnaccentedCases = '';
                
                foreach ($updates as $update) {
                    $normalizedKeywordCases .= " WHEN {$update['id']} THEN ?";
                    $normalizedKeywordUnaccentedCases .= " WHEN {$update['id']} THEN ?";
                }

                $bindings = array_merge(array_column($updates, 'normalized_keyword'),  //
                    array_column($updates, 'normalized_keyword_unaccented'));
                
                $idsPlaceholder = implode(',', $ids);
                $sql = "UPDATE `{$tableName}` 
                        SET `normalized_keyword` = CASE `id` {$normalizedKeywordCases} END,
                            `normalized_keyword_unaccented` = CASE `id` {$normalizedKeywordUnaccentedCases} END
                        WHERE `id` IN ({$idsPlaceholder})";
                
                DB::update($sql, $bindings);
            }

            // Unset variables to free memory
            unset($updates, $ids, $normalizedKeywordCases, $normalizedKeywordUnaccentedCases, $bindings);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->string('normalized_keyword_reversed')->nullable();
            $table->string('normalized_keyword_reversed_unaccented')->nullable();
            $table->integer('normalized_keyword_reversed_length')->nullable();
            $table->integer('normalized_keyword_reversed_unaccented_length')->nullable();

            $table->fullText(['normalized_keyword_reversed']);
            $table->fullText(['normalized_keyword_reversed_unaccented']);
        });

        // Process in chunks for better performance with large datasets
        $tableName = (new SearchKeyword())->getTable();
        
        SearchKeyword::chunkById(self::CHUNK_SIZE, function ($keywords) use ($tableName) {
            $updates = [];
            
            foreach ($keywords as $keyword) {
                $normalizedKeyword = StringHelper::normalize($keyword->keyword, /* transformAccentsIntoLetters = */ true, /* longAccents = */ true);
                $normalizedKeywordUnaccented = StringHelper::normalize($keyword->keyword, /* transformAccentsIntoLetters = */ false);
                
                $updates[] = [
                    'id' => $keyword->id,
                    'normalized_keyword' => $normalizedKeyword,
                    'normalized_keyword_unaccented' => $normalizedKeywordUnaccented,
                ];
            }
            
            // Bulk update using raw SQL with CASE statements for maximum performance
            if (!empty($updates)) {
                $ids = array_column($updates, 'id');
                $normalizedKeywordCases = '';
                $normalizedKeywordUnaccentedCases = '';
                $bindings = [];
                
                foreach ($updates as $update) {
                    $normalizedKeywordCases .= " WHEN {$update['id']} THEN ?";
                    $normalizedKeywordUnaccentedCases .= " WHEN {$update['id']} THEN ?";
                    $bindings[] = $update['normalized_keyword'];
                    $bindings[] = $update['normalized_keyword_unaccented'];
                }
                
                $idsPlaceholder = implode(',', $ids);
                $sql = "UPDATE `{$tableName}` 
                        SET `normalized_keyword` = CASE `id` {$normalizedKeywordCases} END,
                            `normalized_keyword_unaccented` = CASE `id` {$normalizedKeywordUnaccentedCases} END
                        WHERE `id` IN ({$idsPlaceholder})";
                
                DB::update($sql, $bindings);
            }
        });
    }
};
