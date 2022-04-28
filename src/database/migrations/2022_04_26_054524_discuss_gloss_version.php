<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\Models\ForumGroup;

class DiscussGlossVersion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();

            ForumGroup::where('role', 'gloss')->update([
                'role' => 'glossv'
            ]);

            DB::statement('
                update `forum_threads` t
                left join (
                    select `gloss_id`, `__migration_gloss_id` as `old_gloss_id`
                    from `gloss_versions`
                ) lgv on lgv.`old_gloss_id` = t.`entity_id`
                set t.`entity_id` = (
                    select v.`id`
                    from `gloss_versions` v
                    where v.`gloss_id` = lgv.`gloss_id`
                    order by `created_at` desc
                    limit 1
                ),
                t.`entity_type` = \'glossv\'
                where t.`entity_type` = \'gloss\'
            ');

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // noop!
    }
}
