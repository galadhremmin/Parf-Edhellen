<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Carbon\Carbon;
use App\Models\{
    Gloss,
    GlossDetail,
    Language,
    Translation
};
use App\Models\Versioning\{
    GlossVersion,
    GlossDetailVersion,
    TranslationVersion
};

class GlossVersions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // [One-way door] Change all IDs to BIGINT to ensure that all referenced tables use the same ID type.
        $tablesToUpdatePkFor = [
            'accounts' => [],
            'audit_trails' => ['account_id', 'entity_id'],
            'authorization_providers' => [],
            'contributions' => ['account_id', 'language_id', 'reviewed_by_account_id', 'gloss_id', 'sentence_id'],
            'flashcard_results' => ['account_id', 'gloss_id'],
            'flashcards' => ['language_id', 'gloss_group_id'],
            'forum_discussions' => ['account_id'],
            'forum_groups' => [],
            'forum_post_likes' => ['forum_post_id', 'account_id'],
            'forum_posts' => ['parent_forum_post_id', 'account_id', 'forum_thread_id'],
            'forum_threads' => ['entity_id', 'account_id', 'forum_group_id'],
            'gloss_details' => ['gloss_id'],
            'gloss_groups' => [],
            'glosses' => ['language_id', 'gloss_group_id', 'word_id', 'account_id', 'child_gloss_id', 'origin_gloss_id', 'sense_id', 'speech_id'],
            'inflections' => [],
            'keywords' => ['gloss_id', 'word_id', 'sense_id', 'sentence_fragment_id'],
            'languages' => [],
            'roles' => [],
            'search_keywords' => ['entity_id', 'word_id', 'language_id', 'speech_id', 'gloss_group_id'],
            'senses' => [],
            'sentence_fragment_inflection_rels' => ['sentence_fragment_id', 'inflection_id'],
            'sentence_fragments' => ['sentence_id', 'gloss_id', 'speech_id'],
            'sentences' => ['language_id', 'account_id'],
            'speeches' => [],
            'system_errors' => ['account_id'],
            'translations' => ['gloss_id'],
            'words' => ['account_id']
        ];
        Log::info("Updating primary keys and foreign keys (pass 1 of 2)");
        foreach ($tablesToUpdatePkFor as $tableName => $columnNames) {
            Schema::table($tableName, function (Blueprint $table) use ($columnNames) {
                $table->bigIncrements('id')->change();
                foreach ($columnNames as $columnName) {
                    $table->unsignedBigInteger($columnName)->change();
                }
            });
        }

        $tablesToUpdateFksFor = [
            'account_role_rels' => ['account_id', 'role_id'],
            'sentence_translations' => ['sentence_id'],
            'game_word_finder_gloss_groups' => ['gloss_group_id'],
            'mail_setting_overrides' => ['account_id', 'entity_id'],
            'mail_settings' => ['account_id']
        ];
        Log::info("Updating primary keys and foreign keys (pass 2 of 2)");
        foreach ($tablesToUpdateFksFor as $tableName => $columnNames) {
            Schema::table($tableName, function (Blueprint $table) use ($columnNames) {
                foreach ($columnNames as $columnName) {
                    $table->unsignedBigInteger($columnName)->change();
                }
            });
        }

        // [Rollbackable] Create versioning tables
        Log::info("Creating table `gloss_versions`");
        Schema::create('gloss_versions', function (Blueprint $table) {
            $table->collation = 'utf8mb4_unicode_ci';
            $table->charset = 'utf8mb4';
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');
            $table->unsignedInteger('version_change_flags')->nullable();

            $table->foreignId('gloss_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('language_id')
                ->constrained();
            $table->foreignId('word_id')
                ->constrained();
            $table->foreignId('account_id')
                ->constrained();
            $table->foreignId('sense_id')
                ->constrained();
            $table->foreignId('gloss_group_id')
                ->nullable()
                ->constrained();
            $table->foreignId('speech_id')
                ->nullable()
                ->constrained();

            $table->boolean('is_uncertain')->nullable()->default(0);
            $table->boolean('is_rejected')->nullable()->default(0);
            $table->boolean('has_details')->default(0);
            $table->string('etymology', 512)->nullable();
            $table->string('tengwar', 128)->nullable();
            $table->mediumText('source')->nullable();
            $table->mediumText('comments')->nullable();
            $table->string('external_id', 128)->nullable();
            $table->string('label', 16)->nullable();
            $table->unsignedBigInteger('__migration_gloss_id')->nullable(); // temporary field for migration purposes!

            $table->timestamps();
            $table->index(['created_at', 'gloss_id']);
            $table->index('__migration_gloss_id');
        });

        Log::info("Creating table `gloss_detail_versions`");
        Schema::create('gloss_detail_versions', function (Blueprint $table) {
            $table->collation = 'utf8mb4_unicode_ci';
            $table->charset = 'utf8mb4';
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');
            $table->foreignId('gloss_version_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedInteger('order');
            $table->string('category', 128);
            $table->mediumText('text');
            $table->string('type', 16)->nullable();
            $table->timestamps();
        });

        Log::info("Creating table `translation_versions`");
        Schema::create('translation_versions', function (Blueprint $table) {
            $table->collation = 'utf8mb4_unicode_ci';
            $table->charset = 'utf8mb4';
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');
            $table->foreignId('gloss_version_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('translation', 255);
            $table->timestamps();
        });

        Log::info("Adding `latest_gloss_version_id` to `glosses`");
        Schema::table('glosses', function (Blueprint $table) {
            $table->unsignedBigInteger('latest_gloss_version_id')->nullable();
        });

        // There are some cases where glosses have invalid gloss group IDs.
        Log::info("Cleaning data within the `glosses` table");
        Gloss::where('gloss_group_id', 0)->update(['gloss_group_id' => null]);
        Gloss::where('language_id', 0)->update(['language_id' => Language::firstOrCreate([
            'name' => 'Undetermined'
        ], [
            'order' => 999,
            'short_name' => ''
        ])->id]);
        Gloss::where('is_index', 1)->delete();

        // update `is_latest` flag for glosses which appear to lack a 'latest' version
        DB::statement('
            update `glosses` g
            left join (
                select `id` as `latest_gloss_id`, `origin_gloss_id`
                from `glosses`
                where `is_latest` = 1
            ) latest on latest.`origin_gloss_id` = g.`origin_gloss_id`
            set `is_latest` = 1
            where g.`is_latest` = 0 and latest.`latest_gloss_id` is null
        ');

        try {
            DB::beginTransaction();

            // Create a version for each gloss presently in the dictionary, including their translations and details
            Log::info("Migrating old glosses to the `gloss_versions` table (step 1 of 4)");
            DB::statement('
                insert into `gloss_versions` (`gloss_id`, `language_id`,
                    `word_id`, `account_id`, `sense_id`, `gloss_group_id`, `speech_id`, `is_uncertain`,
                    `is_rejected`, `has_details`, `etymology`, `tengwar`, `source`, `comments`, `external_id`,
                    `label`, `__migration_gloss_id`, `created_at`, `updated_at`)
                select `latest_gloss_id`, `language_id`, `word_id`, 
                    `account_id`, `sense_id`, `gloss_group_id`, `speech_id`, `is_uncertain`, `is_rejected`,
                    `has_details`, `etymology`, `tengwar`, `source`, `comments`, `external_id`, `label`,
                    `id`, `created_at`, `updated_at`
                from `glosses`
                    left join (
                        select `id` as `latest_gloss_id`, `origin_gloss_id`
                        from `glosses`
                        where `is_latest` = 1
                    ) latest on latest.`origin_gloss_id` = `glosses`.`origin_gloss_id`
                where `is_latest` = 0
                union
                select `id`, `language_id`, `word_id`, `account_id`, `sense_id`, `gloss_group_id`, `speech_id`,
                    `is_uncertain`, `is_rejected`, `has_details`, `etymology`, `tengwar`, `source`, `comments`,
                    `external_id`, `label`, `id`, `created_at`, `updated_at`
                from `glosses`
                where `is_latest` = 1
            ');

            Log::info("Migrating old glosses to the `gloss_versions` table (step 2 of 4)");
            DB::statement('
                insert into `gloss_detail_versions` (`gloss_version_id`, `order`, `category`, `text`, `type`, `created_at`, `updated_at`)
                select v.`id`, d.`order`, d.`category`, d.`text`, d.`type`, d.`created_at`, d.`updated_at`
                from `gloss_versions` v 
                inner join `gloss_details` d on d.`gloss_id` = v.`__migration_gloss_id`
            ');

            Log::info("Migrating old glosses to the `gloss_versions` table (step 3 of 4)");
            DB::statement('
                insert into `translation_versions` (`gloss_version_id`, `translation`, `created_at`, `updated_at`)
                select v.`id`, t.`translation`, t.`created_at`, t.`updated_at`
                from `gloss_versions` v 
                inner join `translations` t on t.`gloss_id` = v.`__migration_gloss_id`
            ');

            Log::info("Updating `latest_gloss_version_id` columns in `glosses` (step 4 of 4)");
            DB::statement('
                update `glosses` g
                inner join (
                    select `gloss_id`, max(`created_at`) as `created_at`
                    from `gloss_versions`
                    group by `gloss_id`
                ) lgv on lgv.`gloss_id` = g.`id`
                set g.`latest_gloss_version_id` = (
                    select v.`id`
                    from `gloss_versions` v
                    where 
                        v.`gloss_id` = g.`id` and
                        v.`created_at` = lgv.`created_at`
                )
            ');

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

        try {
            DB::beginTransaction();

            // Update references to the latest version of the gloss
            Log::info("Refreshing gloss references (step 1 of 3)");
            DB::statement('
                update `sentence_fragments` f 
                left join (
                    select g.`id`, latest.`latest_gloss_id`
                    from `glosses` g
                        inner join (
                            select `id` as `latest_gloss_id`, coalesce(`origin_gloss_id`, `id`) as `origin_gloss_id`
                            from `glosses`
                            where `is_latest` = 1
                        ) latest on latest.`origin_gloss_id` = g.`origin_gloss_id`
                    where g.`is_latest` = 0
                ) l on l.`id` = f.`gloss_id`
                set f.`gloss_id` = coalesce(l.`latest_gloss_id`, f.`gloss_id`)
                where f.`gloss_id` is not null
            ');

            Log::info("Refreshing gloss references (step 2 of 3)");
            DB::statement('
                update `contributions` c 
                left join (
                    select g.`id`, latest.`latest_gloss_id`
                    from `glosses` g
                        inner join (
                            select `id` as `latest_gloss_id`, coalesce(`origin_gloss_id`, `id`) as `origin_gloss_id`
                            from `glosses`
                            where `is_latest` = 1
                        ) latest on latest.`origin_gloss_id` = g.`origin_gloss_id`
                    where g.`is_latest` = 0
                ) l on l.`id` = c.`gloss_id`
                set c.`gloss_id` = coalesce(l.`latest_gloss_id`, c.`gloss_id`)
                where c.`type` = \'gloss\' and c.`gloss_id` is not null;
            ');

            Log::info("Refreshing gloss references (step 3 of 3)");
            DB::statement('
                update `flashcard_results` f 
                left join (
                    select g.`id`, latest.`latest_gloss_id`
                    from `glosses` g
                        inner join (
                            select `id` as `latest_gloss_id`, coalesce(`origin_gloss_id`, `id`) as `origin_gloss_id`
                            from `glosses`
                            where `is_latest` = 1
                        ) latest on latest.`origin_gloss_id` = g.`origin_gloss_id`
                    where g.`is_latest` = 0
                ) l on l.`id` = f.`gloss_id`
                set f.`gloss_id` = coalesce(l.`latest_gloss_id`, f.`gloss_id`)
            ');
            
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

        // [One-way door] Clean up older revisions.
        Log::info("Purges all outdated glosses from `glosses`");
        Gloss::where('is_latest', 0)->delete();

        // [One-way door] Clean up unused tables. When dropping these columns
        // their data is lost so it cannot be recovered.
        Log::info("Deletes unused tables");
        Schema::dropIfExists('favourites');

        // [One-way door] Remove henceforth unused columns
        Log::info("Final polish pass on tables (step 1 of 2)");
        Schema::table('glosses', function (Blueprint $table) {
            $table->dropIndex('TranslationsIsLatest');
            $table->dropIndex('glosses_origin_gloss_id_index');
            $table->dropColumn('is_index');
            $table->dropColumn('phonetic');
            $table->dropColumn('is_latest');
            $table->dropColumn('child_gloss_id');
            $table->dropColumn('origin_gloss_id');
        });
        Log::info("Final polish pass on tables (step 2 of 2)");
        Schema::table('gloss_details', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translation_versions');
        Schema::dropIfExists('gloss_detail_versions');
        Schema::dropIfExists('gloss_versions');
        Schema::table('glosses', function (Blueprint $table) {
            $table->dropColumn('latest_gloss_version_id');
        });
    }
}
