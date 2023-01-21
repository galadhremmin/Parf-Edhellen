<?php

use App\Interfaces\ISystemLanguageFactory;
use App\Models\ForumPost;
use App\Models\Gloss;
use App\Models\Initialization\Morphs;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{
    DB,
    Schema
};

class KeywordLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $englishLanguage = resolve(ISystemLanguageFactory::class)->language();

        Schema::table('keywords', function (Blueprint $table) {
            $table->unsignedBigInteger('keyword_language_id')->nullable();
            $table->foreign('keyword_language_id')
                ->references('id')
                ->on('languages')
                ->nullOnDelete();
        });

        Schema::table('search_keywords', function (Blueprint $table) {
            $table->unsignedBigInteger('keyword_language_id')->nullable();
            $table->boolean('is_keyword_language_invented')->default(true);
            $table->foreign('keyword_language_id')
                ->references('id')
                ->on('languages')
                ->nullOnDelete();
        });

        DB::statement(
            'UPDATE search_keywords
            SET language_id = (SELECT language_id FROM glosses WHERE glosses.id = search_keywords.entity_id)
            WHERE entity_name = ? 
            AND language_id IS NULL', [
                Morphs::getAlias(Gloss::class)
            ]
        );

        DB::statement(
            'UPDATE search_keywords 
                SET 
                    keyword_language_id = language_id,
                    is_keyword_language_invented = COALESCE(
                        (SELECT is_invented FROM languages WHERE id = search_keywords.language_id),
                        true
                    )
            WHERE 
                search_keywords.entity_name = ?
                AND (
                    search_keywords.word_id = (SELECT word_id FROM glosses where glosses.id = search_keywords.entity_id)
                    OR keyword NOT IN(
                        SELECT translation
                        FROM translations
                        WHERE translations.gloss_id = search_keywords.entity_id
                    )
                )', [
                Morphs::getAlias(Gloss::class)
            ]
        );

        DB::statement(
            'UPDATE keywords 
                SET keyword_language_id = (SELECT language_id FROM glosses WHERE glosses.id = keywords.gloss_id)
            WHERE 
                keywords.gloss_id IS NOT NULL
                AND (word IS NULL OR word = keyword)
                AND (
                    keywords.word_id = (SELECT word_id FROM glosses where glosses.id = keywords.gloss_id)
                    OR keyword NOT IN(
                        SELECT translation
                        FROM translations
                        WHERE translations.gloss_id = keywords.gloss_id
                    )
                )'
        );

        $morphs = [ Morphs::getAlias(Gloss::class), Morphs::getAlias(ForumPost::class) ];
        foreach ($morphs as $morph) {
            DB::statement(
                'UPDATE search_keywords
                SET keyword_language_id = ?,
                    is_keyword_language_invented = 0
                WHERE entity_name = ?
                AND keyword_language_id IS NULL', [
                    $englishLanguage->id,
                    $morph
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropConstrainedForeignId('keyword_language_id');
        });

        Schema::table('search_keywords', function (Blueprint $table) {
            $table->dropConstrainedForeignId('keyword_language_id');
            $table->dropColumn('is_keyword_language_invented');
        });
    }
}
