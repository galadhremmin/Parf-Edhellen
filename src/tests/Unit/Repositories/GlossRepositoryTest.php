<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use Queue;

use Tests\Unit\Traits\CanCreateGloss;
use App\Models\{
    Gloss,
    GlossDetail,
    Translation
};
use App\Repositories\Enumerations\GlossChange;
use App\Repositories\GlossRepository;

class GlossRepositoryTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::tearDown as tearDownGlosses;
        CanCreateGloss::getRepository as getGlossRepository;
    } // ; <-- remedies Visual Studio Code colouring bug

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpGlosses();
        Queue::fake();
    }

    protected function tearDown(): void
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
        $existingGloss = $this->getGlossRepository()->saveGloss($word . ' origin', $sense, $gloss, $translations, $keywords, $details);
        // Create a new gloss, derived from the origin gloss. 
        $newGloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $savedGloss = Gloss::findOrFail($newGloss->id);
        $existingGloss->refresh();

        $this->assertEquals($existingGloss->id, $newGloss->id);
        $this->assertEquals($gloss->id, $newGloss->id);

        // Look for two versions of the gloss
        $this->assertEquals(2, $newGloss->gloss_versions()->count());

        $this->assertEquals($gloss->language_id, $savedGloss->language_id);
        $this->assertEquals($gloss->gloss_group_id, $savedGloss->gloss_group_id);
        $this->assertEquals($gloss->is_uncertain, $savedGloss->is_uncertain);
        $this->assertEquals($gloss->source, $savedGloss->source);
        $this->assertEquals($gloss->comments, $savedGloss->comments);
        $this->assertEquals($gloss->tengwar, $savedGloss->tengwar);
        $this->assertEquals($gloss->speech_id, $savedGloss->speech_id);
        $this->assertEquals($gloss->external_id, $savedGloss->external_id);

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
            array_merge([$word, $savedGloss->sense->word->word], $keywords, array_map(function ($t) {
                return $t->translation;
            }, $translations))
        );
        
        sort($actual);
        sort($expected);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(count($expected), $existingGloss->keywords()->count());

        $actual = $savedGloss->sense->keywords
            ->map(function ($k) {
                return $k->keyword;
            })->toArray();
        $expected = $savedGloss->keywords->merge($existingGloss->keywords)->map(function ($f) {
            return $f->keyword;
        })->toArray();

        sort($actual);
        sort($expected);

        $this->assertEquals($expected, $actual);
    }

    public function testShouldNotSave()
    {
        extract( $this->createGloss(__FUNCTION__) );

        $changed = 0;
        $gloss0 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details, $changed);
        $this->assertEquals(GlossChange::NEW->value, $changed);

        $changed = false;
        $gloss1 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details, $changed);
        $this->assertEquals(GlossChange::NO_CHANGE->value, $changed);

        $this->assertEquals($gloss0->id, $gloss1->id);
    }

    public function testShouldDelete()
    {
        extract( $this->createGloss(__FUNCTION__) );

        $savedGloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
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
        $gloss0 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
        
        $gloss->is_uncertain = false;
        $translations = $this->createTranslations();
        $details = $this->createGlossDetails($gloss);
        $gloss1 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $newWord = $word.' 1';
        $translations = $this->createTranslations(); 
        $details = $this->createGlossDetails($gloss);
        $gloss2 = $this->getGlossRepository()->saveGloss($newWord, $sense, $gloss, $translations, $keywords, $details);

        $newTranslation = uniqid();
        $translations = array_merge(
            $this->createTranslations(),
            [ new Translation(['translation' => $newTranslation]) ]
        ); 
        $details = $this->createGlossDetails($gloss);
        $gloss3 = $this->getGlossRepository()->saveGloss($newWord, $sense, $gloss, $translations, $keywords, $details);

        $this->assertEquals($gloss0->id, $gloss1->id);
        $this->assertEquals($gloss0->id, $gloss2->id);
        $this->assertEquals($gloss0->id, $gloss3->id);

        $versions = $this->getGlossRepository()->getGlossVersions($gloss0->id);

        $this->assertEquals($versions->getLatestVersionId(), $versions->getVersions()->first()->id);
        $this->assertEquals($versions->getLatestVersionId(), $gloss3->latest_gloss_version_id);

        $gloss3->refresh();
        $gloss3Translations = $gloss3->translations->map(function ($t) {
            return $t->translation;
        });
        $actualTranslations = $versions->getVersions()->first()->translations->map(function ($t) {
            return $t->translation;
        });
        $this->assertEquals($gloss3Translations, $actualTranslations);
        $this->assertTrue($gloss3Translations->contains($newTranslation));
    }

    public function testDetectsGlossMetadataChanges()
    {
        $r = $this->getGlossRepository();

        extract( $this->createGloss(__FUNCTION__) );
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
        $gloss->refresh();

        $changed = 0;

        $gloss->source = uniqid();
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details, $changed);
        $versions = $r->getGlossVersions($gloss->id);

        $this->assertTrue(!! $changed);
        $this->assertEquals(GlossChange::METADATA->value, $changed);
        $this->assertEquals(2, $versions->getVersions()->count());
    }

    public function testDetectsGlossDetailsChanges()
    {
        $r = $this->getGlossRepository();

        extract( $this->createGloss(__FUNCTION__) );
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
        $gloss->refresh();

        $changed = 0;

        $newDetail = new GlossDetail([
            'category' => 'Category '.uniqid(),
            'text' => 'Text '.uniqid(), 
            'order' => 100,
        ]);
        $details[] = $newDetail;
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details, $changed);
        $versions = $r->getGlossVersions($gloss->id);

        $this->assertTrue(!! $changed);
        $this->assertEquals(GlossChange::DETAILS->value, $changed);
        $this->assertEquals(1, $gloss->gloss_details->filter(function ($d) use ($newDetail) {
            return $d->text === $newDetail->text && $d->category === $newDetail->category;
        })->count());
        $this->assertEquals(2, $versions->getVersions()->count());
    }

    public function testDetectsGlossTranslationsChanges()
    {
        $r = $this->getGlossRepository();

        extract( $this->createGloss(__FUNCTION__) );
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
        $gloss->refresh();

        $changed = 0;

        $newTranslation = new Translation([
            'translation' => 'Translation '.uniqid()
        ]);
        $translations[] = $newTranslation;
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details, $changed);
        $versions = $r->getGlossVersions($gloss->id);

        $this->assertTrue(!! $changed);
        $this->assertEquals(GlossChange::TRANSLATIONS->value | GlossChange::KEYWORDS->value, $changed);
        $this->assertEquals(1, $gloss->translations->filter(function ($t) use ($newTranslation) {
            return $t->translation === $newTranslation->translation;
        })->count());
        $this->assertEquals(2, $versions->getVersions()->count());
    }

    public function testDetectsGlossKeywordChanges()
    {
        $r = $this->getGlossRepository();

        extract( $this->createGloss(__FUNCTION__) );
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
        $gloss->refresh();

        $changed = 0;

        $newKeyword = 'new keyword '.uniqid();
        $keywords[] = $newKeyword;
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details, $changed);
        $versions = $r->getGlossVersions($gloss->id);

        $this->assertTrue(!! $changed);
        $this->assertEquals(GlossChange::KEYWORDS->value, $changed);
        $this->assertTrue($gloss->keywords->contains(function ($k) use ($newKeyword) {
            return $k->keyword === $newKeyword;
        }));
        $this->assertEquals(2, $versions->getVersions()->count());
    }

    public function testNavigationPropertiesForGloss()
    {
        $r = $this->getGlossRepository();

        extract( $this->createGloss(__FUNCTION__) );
        $r->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $this->assertNotNull($gloss->account);
        $this->assertNotNull($gloss->language);
        $this->assertNotNull($gloss->gloss_group);
        $this->assertNotNull($gloss->speech);
        
        $this->assertEquals(count($translations), $gloss->translations->count());
        $this->assertEquals(count($details), $gloss->gloss_details->count());
    }
}
