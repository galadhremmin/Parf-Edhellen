<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\{
    Gloss,
    GlossGroup,
    Language
};

class EldamoLanguagesUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        GlossGroup::create([
            'name' => 'Eldamo - fan invented',
            'external_link_format' => 'http://eldamo.org/content/words/word-{ExternalID}.html',
            'is_canon' => 0,
            'is_old' => 0,
            'label' => 'fan invented'
        ]);

        GlossGroup::create([
            'name' => 'Eldamo - adaptations',
            'external_link_format' => 'http://eldamo.org/content/words/word-{ExternalID}.html',
            'is_canon' => 0,
            'is_old' => 0,
            'label' => 'adaptation'
        ]);

        $unitTests = GlossGroup::where('name', 'Unit tests')->first();
        if ($unitTests !== null) {
            Gloss::where('gloss_group_id', $unitTests->id)->delete();
            $unitTests->delete();
        }

        Language::create([
            'name' => 'Early Quenya',
            'is_invented' => 1,
            'category' => 'Early Period (1910-1930)',
            'description' => 'Quenya as Tolkien conceived of it in his Early Period (1910-1930 in the terminology of this lexicon). At this stage, it was generally spelled Qenya (and was sometimes called Eldarissa or Eldar).',
            'short_name' => 'eq',
            'is_unusual' => 1,
            'order' => 20
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Language::where('name', 'Early Quenya')->delete();
        GlossGroup::where('name', 'Eldamo - fan invented')->delete();
        GlossGroup::where('name', 'Eldamo - adaptations')->delete();
    }
}
