<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Auth;

use Tests\Unit\Traits\CanCreateGloss;
use App\Models\{
    Gloss,
    Translation
};

class GlossRepositoryTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::tearDown as tearDownGlosses;
        CanCreateGloss::getRepository as getGlossRepository;
    } // ; <-- remedies Visual Studio Code colouring bug

    protected function setUp() 
    {
        parent::setUp();
        $this->setUpGlosses();      
    }

    protected function tearDown()
    {
        $this->tearDownGlosses();
        parent::tearDown();
    }

    /**
     * A basic example of versioning when saving glosses.
     *
     * @return void
     */
    public function testSaveGloss()
    {
        extract( $this->createGloss(__FUNCTION__) );

        // Create an origin gloss, to validate the versioning system. By appending 'origin' to the word string,
        // the next gloss saved (with an unsuffixed word) create a new version of the gloss.
        $existingGloss = $this->getGlossRepository()->saveGloss($word . ' origin', $sense, $gloss, $translations, $keywords);
        // Create a new gloss, derived from the origin gloss. 
        $newGloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords);
        $savedGloss = Gloss::findOrFail($newGloss->id);

        $existingGloss->refresh();

        $this->assertEquals($gloss->language_id, $savedGloss->language_id);
        $this->assertEquals($gloss->gloss_group_id, $savedGloss->gloss_group_id);
        $this->assertEquals($gloss->is_uncertain, $savedGloss->is_uncertain);
        $this->assertEquals($gloss->source, $savedGloss->source);
        $this->assertEquals($gloss->comments, $savedGloss->comments);
        $this->assertEquals($gloss->tengwar, $savedGloss->tengwar);
        $this->assertEquals($gloss->speech_id, $savedGloss->speech_id);
        $this->assertEquals($gloss->external_id, $savedGloss->external_id);

        $this->assertEquals($existingGloss->id, $savedGloss->origin_gloss_id);
        $this->assertEquals($savedGloss->id, $existingGloss->child_gloss_id);
        $this->assertNull($savedGloss->child_gloss_id);

        $this->assertEquals($savedGloss->translations->count(), count($translations));
        $this->assertTrue(
            $savedGloss->translations->every(function ($t) use($translations) {
                return ! empty(array_filter($translations, function ($t0) use ($t) {
                    return $t->translation === $t0->translation;
                }));
            })
        );

        $actual = $savedGloss->keywords->map(function ($k) {
            return $k->keyword;
        })->toArray();
        $expected = array_unique(
            array_merge([$word], array_map(function ($t) {
                return $t->translation;
            }, $translations))
        );
        
        sort($actual);
        sort($expected);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(0, $existingGloss->keywords()->count());

        $actual = $savedGloss->sense->keywords()->whereNull('gloss_id')->get()
            ->map(function ($k) {
                return $k->keyword;
            })->toArray();
        $expected = $keywords;

        sort($actual);
        sort($expected);

        $this->assertEquals($expected, $actual);
    }

    public function testShouldNotSave()
    {
        extract( $this->createGloss(__FUNCTION__) );

        $changed = false;
        $gloss0 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, true, $changed);
        $this->assertTrue($changed);

        $changed = false;
        $gloss1 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, true, $changed);
        $this->assertFalse($changed);

        $this->assertEquals($gloss0->id, $gloss1->id);
    }

    public function testShouldDelete()
    {
        extract( $this->createGloss(__FUNCTION__) );

        $savedGloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords);
        $this->assertEquals($gloss->id, $savedGloss->id);

        $this->getGlossRepository()->deleteGlossWithId($gloss->id);
        
        // resynchronize the model with the database
        $gloss->refresh();

        $this->assertEquals(1, $gloss->is_deleted);
        $this->assertEquals(0, $gloss->keywords()->count());
        $this->assertEquals(0, $gloss->sense->keywords()->count());
    }

    public function testShouldGetVersions()
    {
        extract( $this->createGloss(__FUNCTION__) );
        $numberOfTranslations = count($translations);

        $gloss0 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords);
        
        $gloss->is_uncertain = false;
        $translations = $this->createTranslations();
        $gloss1 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords);

        $newWord = $word.' 1';
        $translations = $this->createTranslations(); 
        $gloss2 = $this->getGlossRepository()->saveGloss($newWord, $sense, $gloss, $translations, $keywords);

        $translations = array_merge(
            $this->createTranslations(),
            [ new Translation(['translation' => 'test '.count($translations)]) ]
        ); 
        $gloss3 = $this->getGlossRepository()->saveGloss($newWord, $sense, $gloss, $translations, $keywords);

        $gloss0->refresh();
        $gloss1->refresh();
        $gloss2->refresh();
        $gloss3->refresh();
        
        $this->assertNull($gloss0->origin_gloss_id);
        $this->assertNull($gloss3->child_gloss_id);
        
        $this->assertEquals($gloss0->id, $gloss1->origin_gloss_id);
        $this->assertEquals($gloss0->id, $gloss2->origin_gloss_id);
        $this->assertEquals($gloss0->id, $gloss3->origin_gloss_id);

        $this->assertEquals($gloss1->id, $gloss0->child_gloss_id);
        $this->assertEquals($gloss2->id, $gloss1->child_gloss_id);
        $this->assertEquals($gloss3->id, $gloss2->child_gloss_id);

        $this->assertEquals($gloss0->word->word, $word);
        $this->assertEquals($gloss1->word->word, $word);
        $this->assertEquals($gloss2->word->word, $newWord);
        $this->assertEquals($gloss3->word->word, $newWord);

        $versions = $this->getGlossRepository()->getVersions($gloss0->id);

        $this->assertEquals(4 * $numberOfTranslations + 1, count($versions));
        $this->assertTrue(!!  $versions[$numberOfTranslations*0]->is_latest);
        $this->assertFalse(!! $versions[$numberOfTranslations*1 + 1]->is_latest);
        $this->assertFalse(!! $versions[$numberOfTranslations*2 + 1]->is_latest);
        $this->assertFalse(!! $versions[$numberOfTranslations*3 + 1]->is_latest);

        $this->assertEquals($gloss3->id, $versions[$numberOfTranslations*0]->id);
        $this->assertEquals($gloss2->id, $versions[$numberOfTranslations*1 + 1]->id);
        $this->assertEquals($gloss1->id, $versions[$numberOfTranslations*2 + 1]->id);
        $this->assertEquals($gloss0->id, $versions[$numberOfTranslations*3 + 1]->id);
    }
}
