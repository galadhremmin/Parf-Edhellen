<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration renames the following entities:
     * - Gloss → LexicalEntry
     * - Translation → Gloss
     * - GlossVersion → LexicalEntryVersion
     * - TranslationVersion → GlossVersion
     * 
     * The renaming is done in a specific order to avoid foreign key constraint issues.
     */
    public function up(): void
    {
        // Step 1: Create new tables with temporary names to avoid conflicts
        $this->createNewTables();
        
        // Step 2: Migrate data from old tables to new tables
        $this->migrateData();
        
        // Step 3: Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        // Step 4: Rename old tables with "old_" prefix for safety
        $this->renameOldTables();
        
        // Step 5: Rename new tables to final names
        $this->renameNewTablesToFinal();
        
        // Step 6: Rename related tables
        $this->renameRelatedTables();
        
        // Step 7: Update foreign key references in other tables
        $this->updateForeignKeys();
        
        // Step 8: Add foreign key constraints that depend on multiple tables
        $this->addDependentForeignKeys();
        
        // Step 9: Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        
        // Step 10: Rename indexes and constraints
        $this->renameIndexesAndConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback not implemented - this is a complex migration
        // that requires careful consideration of data integrity
    }

    /**
     * Create new tables with temporary names to avoid conflicts
     */
    private function createNewTables(): void
    {
        // Create lexical_entry_groups table first (was gloss_groups)
        Schema::create('lexical_entry_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('external_link_format', 1024)->nullable();
            $table->boolean('is_canon')->default(false);
            $table->timestamps();
            $table->integer('is_old')->default(0);
            $table->string('label', 32)->nullable();
        });

        // Create lexical_entries table (was glosses) - no conflict since this table doesn't exist yet
        Schema::create('lexical_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained('languages');
            $table->foreignId('lexical_entry_group_id')->nullable()->constrained('lexical_entry_groups');
            $table->boolean('is_uncertain')->default(false);
            $table->string('etymology', 512)->nullable();
            $table->mediumText('source')->nullable();
            $table->mediumText('comments')->nullable();
            $table->foreignId('word_id')->constrained('words');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->foreignId('account_id')->constrained('accounts');
            $table->string('tengwar', 128)->nullable();
            $table->string('external_id', 128)->nullable();
            $table->foreignId('sense_id')->nullable()->constrained('senses');
            $table->boolean('is_rejected')->default(false);
            $table->foreignId('speech_id')->nullable()->constrained('speeches');
            $table->boolean('has_details')->default(false);
            $table->string('label', 16)->nullable();
            $table->unsignedBigInteger('latest_lexical_entry_version_id')->nullable();
            
            $table->index('word_id', 'WordIDIndex');
            $table->index('language_id', 'LanguageIDIndex');
            $table->index('external_id', 'ExternalID_2');
            $table->index('sense_id', 'idx_senseID');
            $table->index('lexical_entry_group_id', 'TranslationsGroupId');
        });

        // Create new_glosses table (was translations) - using a temporary name to avoid conflicts
        Schema::create('new_glosses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lexical_entry_id')->constrained('lexical_entries')->onDelete('cascade');
            $table->string('translation', 255);
            $table->timestamps();
            
            $table->index('lexical_entry_id', 'new_glosses_lexical_entry_id_index');
        });

        // Create lexical_entry_versions table (was gloss_versions) - no conflict since this table doesn't exist yet
        Schema::create('lexical_entry_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('version_change_flags')->nullable();
            $table->foreignId('lexical_entry_id')->constrained('lexical_entries')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages');
            $table->foreignId('word_id')->constrained('words');
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('sense_id')->constrained('senses');
            $table->foreignId('lexical_entry_group_id')->nullable()->constrained('lexical_entry_groups');
            $table->foreignId('speech_id')->nullable()->constrained('speeches');
            $table->boolean('is_uncertain')->default(false);
            $table->boolean('is_rejected')->default(false);
            $table->boolean('has_details')->default(false);
            $table->string('etymology', 512)->nullable();
            $table->string('tengwar', 128)->nullable();
            $table->mediumText('source')->nullable();
            $table->mediumText('comments')->nullable();
            $table->string('external_id', 128)->nullable();
            $table->string('label', 16)->nullable();
            $table->unsignedBigInteger('__migration_lexical_entry_id')->nullable();
            $table->timestamps();
            
            $table->index('lexical_entry_id', 'lexical_entry_versions_lexical_entry_id_foreign');
            $table->index('language_id', 'lexical_entry_versions_language_id_foreign');
            $table->index('word_id', 'lexical_entry_versions_word_id_foreign');
            $table->index('account_id', 'lexical_entry_versions_account_id_foreign');
            $table->index('sense_id', 'lexical_entry_versions_sense_id_foreign');
            $table->index('lexical_entry_group_id', 'lexical_entry_versions_lexical_entry_group_id_foreign');
            $table->index('speech_id', 'lexical_entry_versions_speech_id_foreign');
            $table->index(['created_at', 'lexical_entry_id'], 'lexical_entry_versions_created_at_lexical_entry_id_index');
            $table->index('__migration_lexical_entry_id', 'lexical_entry_versions___migration_lexical_entry_id_index');
        });

        // Create new_gloss_versions table (was translation_versions) - using temporary name
        Schema::create('new_gloss_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lexical_entry_version_id')->constrained('lexical_entry_versions')->onDelete('cascade');
            $table->string('translation', 255);
            $table->timestamps();
            
            $table->index('lexical_entry_version_id', 'new_gloss_versions_lexical_entry_version_id_foreign');
        });
    }

    /**
     * Rename old tables with "old_" prefix for safety
     */
    private function renameOldTables(): void
    {
        Schema::rename('glosses', 'old_glosses');
        Schema::rename('translations', 'old_translations');
        Schema::rename('gloss_versions', 'old_gloss_versions');
        Schema::rename('translation_versions', 'old_translation_versions');
        Schema::rename('gloss_groups', 'old_gloss_groups');
    }

    /**
     * Rename new tables to final names
     */
    private function renameNewTablesToFinal(): void
    {
        Schema::rename('new_glosses', 'glosses');
        Schema::rename('new_gloss_versions', 'gloss_versions');
    }





    /**
     * Migrate data from old tables to new tables
     */
    private function migrateData(): void
    {
        // Temporarily disable foreign key checks for data migration
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Migrate glosses → lexical_entries (the original glosses table)
        DB::statement('
            INSERT INTO lexical_entries (
                id, language_id, lexical_entry_group_id, is_uncertain, etymology, source, comments,
                word_id, is_deleted, created_at, updated_at, account_id, tengwar, external_id,
                sense_id, is_rejected, speech_id, has_details, label, latest_lexical_entry_version_id
            )
            SELECT 
                id, language_id, gloss_group_id, is_uncertain, etymology, source, comments,
                word_id, is_deleted, created_at, updated_at, account_id, tengwar, external_id,
                sense_id, is_rejected, speech_id, has_details, label, latest_gloss_version_id
            FROM glosses
        ');

        // Migrate translations → new_glosses (the original translations table)
        DB::statement('
            INSERT INTO new_glosses (id, lexical_entry_id, translation, created_at, updated_at)
            SELECT id, gloss_id, translation, created_at, updated_at
            FROM translations
        ');

        // Migrate gloss_versions → lexical_entry_versions (the original gloss_versions table)
        DB::statement('
            INSERT INTO lexical_entry_versions (
                id, version_change_flags, lexical_entry_id, language_id, word_id, account_id,
                sense_id, lexical_entry_group_id, speech_id, is_uncertain, is_rejected, has_details,
                etymology, tengwar, source, comments, external_id, label, __migration_lexical_entry_id,
                created_at, updated_at
            )
            SELECT 
                id, version_change_flags, gloss_id, language_id, word_id, account_id,
                sense_id, gloss_group_id, speech_id, is_uncertain, is_rejected, has_details,
                etymology, tengwar, source, comments, external_id, label, __migration_gloss_id,
                created_at, updated_at
            FROM gloss_versions
        ');

        // Migrate translation_versions → new_gloss_versions (the original translation_versions table)
        DB::statement('
            INSERT INTO new_gloss_versions (id, lexical_entry_version_id, translation, created_at, updated_at)
            SELECT id, gloss_version_id, translation, created_at, updated_at
            FROM translation_versions
        ');

        // Migrate gloss_groups → lexical_entry_groups (the original gloss_groups table)
        DB::statement('
            INSERT INTO lexical_entry_groups (id, name, external_link_format, is_canon, created_at, updated_at, is_old, label)
            SELECT id, name, external_link_format, is_canon, created_at, updated_at, is_old, label
            FROM gloss_groups
        ');

        // Update the latest_lexical_entry_version_id references
        DB::statement('
            UPDATE lexical_entries le
            JOIN lexical_entry_versions lev ON le.id = lev.lexical_entry_id
            SET le.latest_lexical_entry_version_id = lev.id
            WHERE lev.id = (
                SELECT MAX(lev2.id) 
                FROM lexical_entry_versions lev2 
                WHERE lev2.lexical_entry_id = le.id
            )
        ');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Update foreign key references in other tables
     */
    private function updateForeignKeys(): void
    {
        // Update lexical_entry_details table (was gloss_details)
        // This table has gloss_id, not gloss_version_id
        // First, find and drop any existing foreign key constraints on gloss_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'lexical_entry_details' 
            AND COLUMN_NAME = 'gloss_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE lexical_entry_details DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        Schema::table('lexical_entry_details', function (Blueprint $table) {
            $table->renameColumn('gloss_id', 'lexical_entry_id');
            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries');
        });

        // Update lexical_entry_detail_versions table (was gloss_detail_versions)
        // First, find and drop any existing foreign key constraints on gloss_version_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'lexical_entry_detail_versions' 
            AND COLUMN_NAME = 'gloss_version_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE lexical_entry_detail_versions DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        Schema::table('lexical_entry_detail_versions', function (Blueprint $table) {
            $table->renameColumn('gloss_version_id', 'lexical_entry_version_id');
            $table->foreign('lexical_entry_version_id')->references('id')->on('lexical_entry_versions');
        });

        // Update lexical_entry_inflections table (was gloss_inflections)
        // First, find and drop any existing foreign key constraints on gloss_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'lexical_entry_inflections' 
            AND COLUMN_NAME = 'gloss_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE lexical_entry_inflections DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        Schema::table('lexical_entry_inflections', function (Blueprint $table) {
            $table->renameColumn('gloss_id', 'lexical_entry_id');
            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries');
        });
        
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->renameColumn('gloss_group_id', 'lexical_entry_group_id');
            $table->foreign('lexical_entry_group_id')->references('id')->on('lexical_entry_groups');
        });
        
        Schema::table('game_word_finder_gloss_groups', function (Blueprint $table) {
            $table->renameColumn('gloss_group_id', 'lexical_entry_group_id');
            $table->foreign('lexical_entry_group_id')->references('id')->on('lexical_entry_groups');
        });
        
        Schema::table('flashcards', function (Blueprint $table) {
            $table->renameColumn('gloss_group_id', 'lexical_entry_group_id');
            $table->foreign('lexical_entry_group_id')->references('id')->on('lexical_entry_groups');
        });

        // Update sentence_fragments table
        // First, find and drop any existing foreign key constraints on gloss_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'sentence_fragments' 
            AND COLUMN_NAME = 'gloss_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE sentence_fragments DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->renameColumn('gloss_id', 'lexical_entry_id');
            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries');
        });

        // Update contributions table
        // First, find and drop any existing foreign key constraints on gloss_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'contributions' 
            AND COLUMN_NAME = 'gloss_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE contributions DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        Schema::table('contributions', function (Blueprint $table) {
            $table->renameColumn('gloss_id', 'lexical_entry_id');
            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries');
        });

        // Update flashcard_results table
        // First, find and drop any existing foreign key constraints on gloss_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'flashcard_results' 
            AND COLUMN_NAME = 'gloss_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE flashcard_results DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->renameColumn('gloss_id', 'lexical_entry_id');
            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries');
        });

        // Update keywords table
        // First, find and drop any existing foreign key constraints on gloss_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'keywords' 
            AND COLUMN_NAME = 'gloss_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE keywords DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        Schema::table('keywords', function (Blueprint $table) {
            $table->renameColumn('gloss_id', 'lexical_entry_id');
            $table->foreign('lexical_entry_id')->references('id')->on('lexical_entries');
        });

        // Update account_feeds table - this table doesn't have gloss_id, it has content_id
        // We need to update the content_type and content_id to reference the new tables
        // This will be handled in a separate step after the main migration

        // Update search_keywords table (entity_name field) - keeping under 16 characters
        DB::statement("UPDATE search_keywords SET entity_name = 'lexical_entry' WHERE entity_name = 'gloss'");
        DB::statement("UPDATE search_keywords SET entity_name = 'gloss' WHERE entity_name = 'translation'");
        DB::statement("UPDATE search_keywords SET entity_name = 'lex_entry_ver' WHERE entity_name = 'glossv'");
        DB::statement("UPDATE search_keywords SET entity_name = 'gloss_version' WHERE entity_name = 'translationv'");
        
        // Update forum_threads table (entity_type field) - keeping under 16 characters
        DB::statement("UPDATE forum_threads SET entity_type = 'lexical_entry' WHERE entity_type = 'gloss'");
        DB::statement("UPDATE forum_threads SET entity_type = 'gloss' WHERE entity_type = 'translation'");
        DB::statement("UPDATE forum_threads SET entity_type = 'lex_entry_ver' WHERE entity_type = 'glossv'");
        DB::statement("UPDATE forum_threads SET entity_type = 'gloss_version' WHERE entity_type = 'translationv'");

        // Update audit_trails table (entity_type field) - keeping under 16 characters
        DB::statement("UPDATE audit_trails SET entity_type = 'lexical_entry' WHERE entity_type = 'gloss'");
        DB::statement("UPDATE audit_trails SET entity_type = 'gloss' WHERE entity_type = 'translation'");
        DB::statement("UPDATE audit_trails SET entity_type = 'lex_entry_ver' WHERE entity_type = 'glossv'");
        DB::statement("UPDATE audit_trails SET entity_type = 'gloss_version' WHERE entity_type = 'translationv'");

        // Update mail_setting_overrides table (entity_type field) - keeping under 16 characters
        DB::statement("UPDATE mail_setting_overrides SET entity_type = 'lexical_entry' WHERE entity_type = 'gloss'");
        DB::statement("UPDATE mail_setting_overrides SET entity_type = 'gloss' WHERE entity_type = 'translation'");
        DB::statement("UPDATE mail_setting_overrides SET entity_type = 'lex_entry_ver' WHERE entity_type = 'glossv'");
        DB::statement("UPDATE mail_setting_overrides SET entity_type = 'gloss_version' WHERE entity_type = 'translationv'");

        // Update account_feed_refresh_times table (feed_content_type field) - keeping under 16 characters
        DB::statement("UPDATE account_feed_refresh_times SET feed_content_type = 'lexical_entry' WHERE feed_content_type = 'gloss'");
        DB::statement("UPDATE account_feed_refresh_times SET feed_content_type = 'gloss' WHERE feed_content_type = 'translation'");
        DB::statement("UPDATE account_feed_refresh_times SET feed_content_type = 'lex_entry_ver' WHERE feed_content_type = 'glossv'");
        DB::statement("UPDATE account_feed_refresh_times SET feed_content_type = 'gloss_version' WHERE feed_content_type = 'translationv'");

        // Update account_feeds table (content_type field) - keeping under 16 characters
        DB::statement("UPDATE account_feeds SET content_type = 'lexical_entry' WHERE content_type = 'gloss'");
        DB::statement("UPDATE account_feeds SET content_type = 'gloss' WHERE content_type = 'translation'");
        DB::statement("UPDATE account_feeds SET content_type = 'lex_entry_ver' WHERE content_type = 'glossv'");
        DB::statement("UPDATE account_feeds SET content_type = 'gloss_version' WHERE content_type = 'translationv'");

        // Update forum_groups table (role field) - keeping under 16 characters
        DB::statement("UPDATE forum_groups SET role = 'lexical_entry' WHERE role = 'gloss'");
        DB::statement("UPDATE forum_groups SET role = 'gloss' WHERE role = 'translation'");
        DB::statement("UPDATE forum_groups SET role = 'lex_entry_ver' WHERE role = 'glossv'");
        DB::statement("UPDATE forum_groups SET role = 'gloss_version' WHERE role = 'translationv'");

        // Update account_feeds table to reference new tables
        $this->updateAccountFeeds();
    }

    /**
     * Rename related tables
     */
    private function renameRelatedTables(): void
    {
        // Rename gloss_details to lexical_entry_details
        Schema::rename('gloss_details', 'lexical_entry_details');
        
        // Rename gloss_detail_versions to lexical_entry_detail_versions
        Schema::rename('gloss_detail_versions', 'lexical_entry_detail_versions');
        
        // Rename gloss_inflections to lexical_entry_inflections
        Schema::rename('gloss_inflections', 'lexical_entry_inflections');
    }

    /**
     * Add foreign key constraints that depend on multiple tables
     */
    private function addDependentForeignKeys(): void
    {
        // Add foreign key constraint for latest_lexical_entry_version_id
        Schema::table('lexical_entries', function (Blueprint $table) {
            $table->foreign('latest_lexical_entry_version_id')->references('id')->on('lexical_entry_versions');
        });
    }

    /**
     * Update account_feeds table to reference new tables
     */
    private function updateAccountFeeds(): void
    {
        // Update content_type from 'gloss' to 'lexical_entry'
        DB::statement("UPDATE account_feeds SET content_type = 'lexical_entry' WHERE content_type = 'gloss'");
        
        // Update content_type from 'translation' to 'gloss'
        DB::statement("UPDATE account_feeds SET content_type = 'gloss' WHERE content_type = 'translation'");
        
        // Update content_type from 'gloss_version' to 'lexical_entry_version'
        DB::statement("UPDATE account_feeds SET content_type = 'lexical_entry_version' WHERE content_type = 'gloss_version'");
        
        // Update content_type from 'translation_version' to 'gloss_version'
        DB::statement("UPDATE account_feeds SET content_type = 'gloss_version' WHERE content_type = 'translation_version'");
    }

    /**
     * Rename indexes and constraints to follow industry standard naming
     */
    private function renameIndexesAndConstraints(): void
    {
        try {
            // Rename indexes in lexical_entry_details (was gloss_details)
            DB::statement('ALTER TABLE lexical_entry_details RENAME INDEX idx_glosses TO lexical_entry_details_lexical_entry_id_index');
        } catch (\Exception $e) {
            // Ignore the error if the index doesn't exist
        }

        try {
            // Rename indexes in lexical_entry_detail_versions (was gloss_detail_versions)
            DB::statement('ALTER TABLE lexical_entry_detail_versions RENAME INDEX gloss_detail_versions_gloss_version_id_foreign TO lexical_entry_detail_versions_lexical_entry_version_id_foreign');
        } catch (\Exception $e) {
            // Ignore the error if the index doesn't exist
        }

        try {
            // Rename indexes in lexical_entry_inflections (was gloss_inflections)
            DB::statement('ALTER TABLE lexical_entry_inflections RENAME INDEX gloss_inflections_gloss_id_index TO lexical_entry_inflections_lexical_entry_id_index');
        } catch (\Exception $e) {
            // Ignore the error if the index doesn't exist
        }

        try {
            // Rename indexes in keywords (only the ones that need renaming)
            DB::statement('ALTER TABLE keywords RENAME INDEX KeywordsTranslationId TO keywords_lexical_entry_id_index');
        } catch (\Exception $e) {
            // Ignore the error if the index doesn't exist
        }

        try {
            // Rename indexes in keywords (only the ones that need renaming)
            DB::statement('ALTER TABLE keywords RENAME INDEX WordGlossFragmentRelation TO keywords_word_lexical_entry_fragment_index');
        } catch (\Exception $e) {
            // Ignore the error if the index doesn't exist
        }
    }
}; 