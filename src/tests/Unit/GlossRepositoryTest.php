<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Auth;

use App\Repositories\GlossRepository;
use App\Models\{
    Account,
    Gloss,
    GlossGroup,
    Language,
    Speech,
    Translation
};

class GlossRepositoryTest extends TestCase
{
    private $_glossRepository;
    private $_group;
    private $_glosses;

    protected function setUp() 
    {
        parent::setUp();

        $this->_glossRepository = resolve(GlossRepository::class);
        $this->_group = GlossGroup::firstOrCreate([
            'name' => 'Unit tests',
            'is_canon' => true
        ]);
        $this->_glosses = $this->_group->glosses;
        $this->clearGlosses();

        Auth::shouldReceive('user')->andReturn($user = Account::findOrFail(1));        
    }

    protected function clearGlosses()
    {
        foreach ($this->_glosses as $gloss)
        {
            $gloss->keywords()->delete();
            $gloss->translations()->delete();
            
            $sense = $gloss->sense;
            if ($sense) {
                $sense->keywords()->delete();
                $sense->delete();
            }

            $gloss->delete();
        }

        $this->_glosses = [];
    }

    protected function tearDown()
    {
        $this->clearGlosses();
        
        parent::tearDown();
    }

    /**
     * Creates a gloss.
     *
     * @param string $word
     * @param string $sense
     * @param string $method
     * @return void
     */
    private function createGloss(string $method, string $word = 'test-word', string $sense = 'test-sense')
    {
        $accountId = Account::firstOrFail()->id;
        $languageId = Language::where('name', 'Sindarin')
            ->firstOrFail()->id;
        $speechId = Speech::where('name', 'verb')
            ->firstOrFail()->id;

        $gloss = new Gloss;
        $gloss->account_id = $accountId;
        $gloss->language_id = $languageId;
        $gloss->gloss_group_id = $this->_group->id;
        $gloss->is_uncertain = 1;
        $gloss->source = 'Unit test';
        $gloss->comments = 'This gloss was created in an unit test.';
        $gloss->tengwar = 'yljjh6';
        $gloss->speech_id = $speechId;
        $gloss->external_id = 'UA-Unit-GlossRepository-'.$method;

        $translations = [
            new Translation([ 'translation' => 'test 0' ]),
            new Translation([ 'translation' => 'test 1' ]),
            new Translation([ 'translation' => 'test 2' ])
        ];

        $keywords = [
            'test 3',
            'test 4', 
            'test 5'
        ];

        return [
            'word' => $word,
            'sense' => $sense,
            'gloss' => $gloss,
            'translations' => $translations,
            'keywords' => $keywords
        ];
    }

    /**
     * A basic example of versioning when saving glosses.
     *
     * @return void
     */
    public function testSaveGloss()
    {
        extract( $this->createGloss(__FUNCTION__, 'test-word', 'test-sense') );

        // Create an origin gloss, to validate the versioning system. By appending 'origin' to the word string,
        // the next gloss saved (with an unsuffixed word) create a new version of the gloss.
        $existingGloss = $this->_glossRepository->saveGloss($word . ' origin', $sense, $gloss, $translations, $keywords);
        // Create a new gloss, derived from the origin gloss. 
        $newGloss = $this->_glossRepository->saveGloss($word, $sense, $gloss, $translations, $keywords);
        $savedGloss = Gloss::findOrFail($newGloss->id);

        $this->_glosses[] = $existingGloss;
        $this->_glosses[] = $savedGloss;

        $this->assertEquals($gloss->language_id, $savedGloss->language_id);
        $this->assertEquals($gloss->gloss_group_id, $savedGloss->gloss_group_id);
        $this->assertEquals($gloss->is_uncertain, $savedGloss->is_uncertain);
        $this->assertEquals($gloss->source, $savedGloss->source);
        $this->assertEquals($gloss->comments, $savedGloss->comments);
        $this->assertEquals($gloss->tengwar, $savedGloss->tengwar);
        $this->assertEquals($gloss->speech_id, $savedGloss->speech_id);
        $this->assertEquals($gloss->external_id, $savedGloss->external_id);

        $this->assertEquals($existingGloss->id, $savedGloss->origin_gloss_id);
        $this->assertEquals($savedGloss->id, Gloss::findOrFail($existingGloss->id)->child_gloss_id);
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
        extract( $this->createGloss(__FUNCTION__, 'test-word', 'test-sense') );

        $changed = false;
        $gloss0 = $this->_glossRepository->saveGloss($word, $sense, $gloss, $translations, $keywords, true, $changed);
        $this->assertTrue($changed);

        $changed = false;
        $gloss1 = $this->_glossRepository->saveGloss($word, $sense, $gloss, $translations, $keywords, true, $changed);
        $this->assertFalse($changed);

        $this->assertEquals($gloss0->id, $gloss1->getLatestVersion()->id);

        $this->_glosses[] = $gloss0;
        $this->_glosses[] = $gloss1;
    }
}
