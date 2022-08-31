<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{
    DB,
    Schema
};
use Ramsey\Uuid\Uuid;
use App\Helpers\SentenceBuilders\SentenceBuilder;
use App\Models\{
    GlossInflection,
    SearchKeyword,
    SentenceFragment
};

class GlossInflections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gloss_inflections', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            
            $table->uuid('inflection_group_uuid');
            $table->unsignedBigInteger('gloss_id');
            $table->unsignedBigInteger('language_id');
            $table->unsignedBigInteger('speech_id');
            $table->unsignedBigInteger('inflection_id');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->boolean('is_neologism')->default(false);
            $table->boolean('is_rejected')->default(false);
            $table->string('source', 64)->nullable();
            $table->unsignedSmallInteger('order', false)->default(0);
            $table->timestamps();

            $table->index('inflection_group_uuid');
            $table->index('gloss_id');
        });

        Schema::table('search_keywords', function (Blueprint $table) {
            $table->uuid('inflection_group_uuid')->nullable();
        });

        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->uuid('inflection_group_uuid')->nullable();
        });

        try {
            DB::beginTransaction();

            $query = SentenceFragment::where('type', SentenceBuilder::TYPE_CODE_WORD) //
                ->whereNotNull('gloss_id') //
                ->with(['inflection_associations', 'sentence']);
            foreach ($query->cursor() as $f) {
                $inflection_group_uuid = Uuid::uuid4();
                $order = 0;

                foreach ($f->inflection_associations__deprecated as $i) {
                    GlossInflection::create([
                        'inflection_group_uuid' => $inflection_group_uuid,
                        'gloss_id'              => $f->gloss_id,
                        'inflection_id'         => $i->inflection_id,
                        'account_id'            => $f->sentence->account_id,
                        'language_id'           => $f->sentence->language_id,
                        'speech_id'             => $f->speech_id,
                        'order'                 => $order++ * 10
                    ]);
                }

                SearchKeyword::where('entity_name', 'fragment')
                    ->where('entity_id', $f->id)
                    ->update([
                        'inflection_group_uuid' => $inflection_group_uuid
                    ]);

                $f->inflection_group_uuid = $inflection_group_uuid;
                $f->save();
            }

            DB::commit();
        } catch (\Exception $ex) {
            // ignore exception
            DB::rollBack();
            echo $ex->getMessage();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gloss_inflections');
        Schema::table('search_keywords', function (Blueprint $table) {
            $table->dropColumn('inflection_group_uuid');
        });
        Schema::table('sentence_fragments', function (Blueprint $table) {
            $table->dropColumn('inflection_group_uuid');
        });
    }
}
