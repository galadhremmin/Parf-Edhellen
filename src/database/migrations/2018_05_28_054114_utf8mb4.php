<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class Utf8mb4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'account_role_rels',
            'accounts',
            'audit_trails',
            'authorization_providers',
            'contributions',
            'favourites',
            'flashcard_results',
            'flashcards',
            'forum_discussions',
            'forum_post_likes',
            'forum_posts',
            'forum_threads',
            'gloss_details',
            'gloss_groups',
            'glosses',
            'inflections',
            'jobs',
            'keywords',
            'languages',
            'mail_setting_overrides',
            'mail_settings',
            'migrations',
            'roles',
            'senses', 
            'sentence_fragment_inflection_rels', 
            'sentence_fragments', 
            'sentences', 
            'speeches', 
            'system_errors', 
            'translations', 
            'version', 
            'words'
        ];

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('email', 128)->change();
            $table->string('identity', 64)->change();
        });
        
        foreach ($tables as $table) {
            DB::connection(null)->unprepared('ALTER TABLE '.$table.' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Encoding is a one-way door. You cannot rollback without lossiness.

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('email', 255)->change();
            $table->string('identity', 255)->change();
        });
    }
}
