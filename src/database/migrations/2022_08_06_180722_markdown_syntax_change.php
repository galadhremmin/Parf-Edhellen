<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Account,
    ForumPost,
    Gloss,
    GlossDetail,
    Sentence
};
use App\Models\Versioning\{
    GlossVersion,
    GlossDetailVersion
};

class MarkdownSyntaxChange extends Migration
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
            foreach (ForumPost::cursor() as $p) {
                $p->content = $this->migrateFormat($p->content);
                $p->save();
            }
            DB::commit();
            print_r('Completed forum posts.');
            DB::beginTransaction();
            foreach (Gloss::whereNotNull('comments')->cursor() as $p) {
                $p->comments = $this->migrateFormat($p->comments);
                $p->save();
            }
            DB::commit();
            print_r('Completed glosses.');
            DB::beginTransaction();
            foreach (GlossVersion::whereNotNull('comments')->cursor() as $p) {
                $p->comments = $this->migrateFormat($p->comments);
                $p->save();
            }
            DB::commit();
            print_r('Completed gloss versions.');
            DB::beginTransaction();
            foreach (GlossDetail::whereNotNull('text')->cursor() as $p) {
                $p->text = $this->migrateFormat($p->text);
                $p->save();
            }
            DB::commit();
            print_r('Completed forum gloss details.');
            DB::beginTransaction();
            foreach (GlossDetailVersion::whereNotNull('text')->cursor() as $p) {
                $p->text = $this->migrateFormat($p->text);
                $p->save();
            }
            DB::commit();
            print_r('Completed forum gloss detail versions.');
            DB::beginTransaction();
            foreach (Account::whereNotNull('profile')->cursor() as $p) {
                $p->profile = $this->migrateFormat($p->profile);
                $p->save();
            }
            DB::commit();
            print_r('Completed forum accounts.');
            DB::beginTransaction();
            foreach (Sentence::whereNotNull('long_description')->cursor() as $p) {
                $p->long_description = $this->migrateFormat($p->long_description);
                $p->save();
            }
            DB::commit();
            print_r('Completed sentences.');
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    private function migrateFormat($content)
    {
        if (empty($content)) {
            return $content;
        }

        $content = preg_replace('/@([a-zA-Z_]+)\\|(.+)@/', '@\\1:\\2@', $content);
        $content = preg_replace('/\\[\\[([a-zA-Z_]+)\\|(.+)\\]\\]/', '[[\\1:\\2]]', $content);
        return $content;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
