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
     * This migration adds missing foreign key constraints to optimize the database schema.
     * It ensures referential integrity for all foreign key relationships.
     */
    public function up(): void
    {
        // Temporarily disable foreign key checks for the migration
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Add missing foreign key constraints
        $this->addAccountForeignKeys();
        $this->addAuditTrailForeignKeys();
        $this->addContributionForeignKeys();
        $this->addFlashcardForeignKeys();
        $this->addForumForeignKeys();
        $this->addKeywordForeignKeys();
        $this->addLexicalEntryInflectionForeignKeys();
        $this->addSearchKeywordForeignKeys();
        $this->addSentenceForeignKeys();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Temporarily disable foreign key checks for the rollback
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Drop foreign key constraints in reverse order
        $this->dropSentenceForeignKeys();
        $this->dropSearchKeywordForeignKeys();
        $this->dropLexicalEntryInflectionForeignKeys();
        $this->dropKeywordForeignKeys();
        $this->dropForumForeignKeys();
        $this->dropFlashcardForeignKeys();
        $this->dropContributionForeignKeys();
        $this->dropAuditTrailForeignKeys();
        $this->dropAccountForeignKeys();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Add foreign key constraints for account-related tables
     */
    private function addAccountForeignKeys(): void
    {
        // accounts.authorization_provider_id -> authorization_providers.id
        // First, change the column type to match the referenced primary key
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('authorization_provider_id')->nullable()->change();
            $table->foreign('authorization_provider_id')->references('id')->on('authorization_providers');
        });

        // account_feeds.account_id -> accounts.id
        Schema::table('account_feeds', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // account_feed_refresh_times.account_id -> accounts.id
        Schema::table('account_feed_refresh_times', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // account_role_rels.account_id -> accounts.id
        Schema::table('account_role_rels', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // account_role_rels.role_id -> roles.id
        Schema::table('account_role_rels', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        // audit_trails.account_id -> accounts.id
        Schema::table('audit_trails', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // contributions.account_id -> accounts.id
        Schema::table('contributions', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // contributions.reviewed_by_account_id -> accounts.id
        Schema::table('contributions', function (Blueprint $table) {
            $table->foreign('reviewed_by_account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // flashcard_results.account_id -> accounts.id
        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // forum_discussions.account_id -> accounts.id
        Schema::table('forum_discussions', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // forum_posts.account_id -> accounts.id
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // forum_post_likes.account_id -> accounts.id
        Schema::table('forum_post_likes', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // forum_threads.account_id -> accounts.id
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // lexical_entry_inflections.account_id -> accounts.id
        Schema::table('lexical_entry_inflections', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
        });

        // mail_settings.account_id -> accounts.id
        Schema::table('mail_settings', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // mail_setting_overrides.account_id -> accounts.id
        Schema::table('mail_setting_overrides', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // sentences.account_id -> accounts.id
        Schema::table('sentences', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // system_errors.account_id -> accounts.id
        Schema::table('system_errors', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
        });

        // words.account_id -> accounts.id
        Schema::table('words', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->change();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
        });
    }

    /**
     * Add foreign key constraints for audit trail related tables
     */
    private function addAuditTrailForeignKeys(): void
    {
        // account_feeds.audit_trail_action_id -> audit_trail_actions.id (if table exists)
        // Note: This might reference an enum or lookup table that doesn't exist yet
        // For now, we'll skip this as it appears to be an action type identifier

        // account_feeds.audit_trail_id -> audit_trails.id
        Schema::table('account_feeds', function (Blueprint $table) {
            $table->foreign('audit_trail_id')->references('id')->on('audit_trails');
        });

        // audit_trails.action_id -> audit_trail_actions.id (if table exists)
        // Note: This might reference an enum or lookup table that doesn't exist yet
        // For now, we'll skip this as it appears to be an action type identifier

        // audit_trails.entity_id -> various entity tables (polymorphic relationship)
        // Note: This is a polymorphic foreign key, so we can't add a traditional constraint
    }

    /**
     * Add foreign key constraints for contribution related tables
     */
    private function addContributionForeignKeys(): void
    {
        // contributions.language_id -> languages.id
        Schema::table('contributions', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
        });

        // contributions.sentence_id -> sentences.id
        Schema::table('contributions', function (Blueprint $table) {
            $table->foreign('sentence_id')->references('id')->on('sentences')->onDelete('set null');
        });

        // contributions.dependent_on_contribution_id -> contributions.id (self-referencing)
        Schema::table('contributions', function (Blueprint $table) {
            $table->foreign('dependent_on_contribution_id')->references('id')->on('contributions')->onDelete('cascade');
        });

        // contributions.approved_as_entity_id -> various entity tables (polymorphic relationship)
        // Note: This is a polymorphic foreign key, so we can't add a traditional constraint
    }

    /**
     * Add foreign key constraints for flashcard related tables
     */
    private function addFlashcardForeignKeys(): void
    {
        // flashcards.language_id -> languages.id
        Schema::table('flashcards', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
        });

        // flashcard_results.flashcard_id -> flashcards.id
        // First, change the column type to match the referenced primary key
        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->unsignedBigInteger('flashcard_id')->change();
            $table->foreign('flashcard_id')->references('id')->on('flashcards')->onDelete('cascade');
        });
    }

    /**
     * Add foreign key constraints for forum related tables
     */
    private function addForumForeignKeys(): void
    {
        // forum_posts.forum_thread_id -> forum_threads.id
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->foreign('forum_thread_id')->references('id')->on('forum_threads')->onDelete('cascade');
        });

        // forum_posts.parent_forum_post_id -> forum_posts.id (self-referencing)
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->foreign('parent_forum_post_id')->references('id')->on('forum_posts')->onDelete('set null');
        });

        // forum_post_likes.forum_post_id -> forum_posts.id
        Schema::table('forum_post_likes', function (Blueprint $table) {
            $table->foreign('forum_post_id')->references('id')->on('forum_posts')->onDelete('cascade');
        });

        // forum_threads.forum_group_id -> forum_groups.id
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->foreign('forum_group_id')->references('id')->on('forum_groups')->onDelete('cascade');
        });

        // forum_threads.entity_id -> various entity tables (polymorphic relationship)
        // Note: This is a polymorphic foreign key, so we can't add a traditional constraint
    }

    /**
     * Add foreign key constraints for keyword related tables
     */
    private function addKeywordForeignKeys(): void
    {
        // keywords.sense_id -> senses.id
        Schema::table('keywords', function (Blueprint $table) {
            $table->foreign('sense_id')->references('id')->on('senses')->onDelete('cascade');
        });

        // keywords.sentence_fragment_id -> sentence_fragments.id
        Schema::table('keywords', function (Blueprint $table) {
            $table->foreign('sentence_fragment_id')->references('id')->on('sentence_fragments')->onDelete('cascade');
        });

        // keywords.word_id -> words.id
        Schema::table('keywords', function (Blueprint $table) {
            $table->foreign('word_id')->references('id')->on('words')->onDelete('cascade');
        });
    }

    /**
     * Add foreign key constraints for lexical entry inflection related tables
     */
    private function addLexicalEntryInflectionForeignKeys(): void
    {
        // lexical_entry_inflections.inflection_id -> inflections.id
        Schema::table('lexical_entry_inflections', function (Blueprint $table) {
            $table->foreign('inflection_id')->references('id')->on('inflections');
        });

        // lexical_entry_inflections.language_id -> languages.id
        Schema::table('lexical_entry_inflections', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
        });

        // lexical_entry_inflections.sentence_fragment_id -> sentence_fragments.id
        Schema::table('lexical_entry_inflections', function (Blueprint $table) {
            $table->foreign('sentence_fragment_id')->references('id')->on('sentence_fragments');
        });

        // lexical_entry_inflections.sentence_id -> sentences.id
        Schema::table('lexical_entry_inflections', function (Blueprint $table) {
            $table->foreign('sentence_id')->references('id')->on('sentences');
        });

        // lexical_entry_inflections.speech_id -> speeches.id
        Schema::table('lexical_entry_inflections', function (Blueprint $table) {
            $table->foreign('speech_id')->references('id')->on('speeches')->onDelete('set null');
        });
    }

    /**
     * Add foreign key constraints for search keyword related tables
     */
    private function addSearchKeywordForeignKeys(): void
    {
        // search_keywords.language_id -> languages.id
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
        });

        // search_keywords.speech_id -> speeches.id
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->foreign('speech_id')->references('id')->on('speeches')->onDelete('set null');
        });

        // search_keywords.word_id -> words.id
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->foreign('word_id')->references('id')->on('words');
        });

        // search_keywords.entity_id -> various entity tables (polymorphic relationship)
        // Note: This is a polymorphic foreign key, so we can't add a traditional constraint

        // game_word_finder_languages.language_id -> languages.id
        // First, change the column type to match the referenced primary key
        Schema::table('game_word_finder_languages', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->change();
            $table->foreign('language_id')->references('id')->on('languages');
        });
    }

    /**
     * Add foreign key constraints for sentence related tables
     */
    private function addSentenceForeignKeys(): void
    {
        // sentences.language_id -> languages.id
        Schema::table('sentences', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
        });

        // sentence_fragments.sentence_id -> sentences.id
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->foreign('sentence_id')->references('id')->on('sentences');
        });

        // sentence_fragments.speech_id -> speeches.id
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->foreign('speech_id')->references('id')->on('speeches');
        });

        // sentence_fragment_inflection_rels.inflection_id -> inflections.id
        Schema::table('sentence_fragment_inflection_rels', function (Blueprint $table) {
            $table->foreign('inflection_id')->references('id')->on('inflections');
        });

        // sentence_fragment_inflection_rels.sentence_fragment_id -> sentence_fragments.id
        Schema::table('sentence_fragment_inflection_rels', function (Blueprint $table) {
            $table->foreign('sentence_fragment_id')->references('id')->on('sentence_fragments');
        });

        // sentence_translations.sentence_id -> sentences.id
        Schema::table('sentence_translations', function (Blueprint $table) {
            $table->foreign('sentence_id')->references('id')->on('sentences');
        });
    }

    /**
     * Drop foreign key constraints for account-related tables
     */
    private function dropAccountForeignKeys(): void
    {
        $this->dropForeignKeys([
            'accounts' => ['authorization_provider_id'],
            'account_feeds' => ['account_id'],
            'account_feed_refresh_times' => ['account_id'],
            'account_role_rels' => ['account_id', 'role_id'],
            'audit_trails' => ['account_id'],
            'contributions' => ['account_id', 'reviewed_by_account_id'],
            'flashcard_results' => ['account_id'],
            'forum_discussions' => ['account_id'],
            'forum_posts' => ['account_id'],
            'forum_post_likes' => ['account_id'],
            'forum_threads' => ['account_id'],
            'lexical_entry_inflections' => ['account_id'],
            'mail_settings' => ['account_id'],
            'mail_setting_overrides' => ['account_id'],
            'sentences' => ['account_id'],
            'system_errors' => ['account_id'],
            'words' => ['account_id'],
        ]);
    }

    /**
     * Drop foreign key constraints for audit trail related tables
     */
    private function dropAuditTrailForeignKeys(): void
    {
        $this->dropForeignKeys([
            'account_feeds' => ['audit_trail_id'],
        ]);
    }

    /**
     * Drop foreign key constraints for contribution related tables
     */
    private function dropContributionForeignKeys(): void
    {
        $this->dropForeignKeys([
            'contributions' => ['language_id', 'sentence_id', 'dependent_on_contribution_id'],
        ]);
    }

    /**
     * Drop foreign key constraints for flashcard related tables
     */
    private function dropFlashcardForeignKeys(): void
    {
        $this->dropForeignKeys([
            'flashcards' => ['language_id'],
            'flashcard_results' => ['flashcard_id'],
        ]);
    }

    /**
     * Drop foreign key constraints for forum related tables
     */
    private function dropForumForeignKeys(): void
    {
        $this->dropForeignKeys([
            'forum_posts' => ['forum_thread_id', 'parent_forum_post_id'],
            'forum_post_likes' => ['forum_post_id'],
            'forum_threads' => ['forum_group_id'],
        ]);
    }

    /**
     * Drop foreign key constraints for keyword related tables
     */
    private function dropKeywordForeignKeys(): void
    {
        $this->dropForeignKeys([
            'keywords' => ['sense_id', 'sentence_fragment_id', 'word_id'],
        ]);
    }

    /**
     * Drop foreign key constraints for lexical entry inflection related tables
     */
    private function dropLexicalEntryInflectionForeignKeys(): void
    {
        $this->dropForeignKeys([
            'lexical_entry_inflections' => ['inflection_id', 'language_id', 'sentence_fragment_id', 'sentence_id', 'speech_id'],
        ]);
    }

    /**
     * Drop foreign key constraints for search keyword related tables
     */
    private function dropSearchKeywordForeignKeys(): void
    {
        $this->dropForeignKeys([
            'search_keywords' => ['language_id', 'speech_id', 'word_id'],
            'game_word_finder_languages' => ['language_id'],
        ]);
    }

    /**
     * Drop foreign key constraints for sentence related tables
     */
    private function dropSentenceForeignKeys(): void
    {
        $this->dropForeignKeys([
            'sentences' => ['language_id'],
            'sentence_fragments' => ['sentence_id', 'speech_id'],
            'sentence_fragment_inflection_rels' => ['inflection_id', 'sentence_fragment_id'],
            'sentence_translations' => ['sentence_id'],
        ]);
    }

    /**
     * Helper method to drop foreign key constraints
     */
    private function dropForeignKeys(array $tableConstraints): void
    {
        foreach ($tableConstraints as $tableName => $columns) {
            foreach ($columns as $column) {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = 'elfdict_v4' 
                    AND TABLE_NAME = ? 
                    AND COLUMN_NAME = ? 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [$tableName, $column]);
                
                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE {$tableName} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                }
            }
        }
    }
};
