<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Auth;
use Route;
use Queue;

use Tests\Unit\Traits\CanCreateGloss;
use App\Adapters\BookAdapter;
use App\Models\{
    Gloss,
    Translation,
    SentenceFragmentInflectionRel
};

class BookAdapterTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::tearDown as tearDownGlosses;
        CanCreateGloss::getRepository as getGlossRepository;
    } // ; <-- remedies Visual Studio Code colouring bug

    private $_adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpGlosses();
        Queue::fake();
        
        $this->_adapter = resolve(BookAdapter::class);
    }

    protected function tearDown(): void
    {
        $this->tearDownGlosses();
        parent::tearDown();
    }

    public function testAdaptGloss()
    {
        extract( $this->createGloss(__FUNCTION__) );
        $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $languages   = new Collection([ $gloss->language ]);
        $inflections = [];
        $comments    = [ $gloss->id => 10 ];
        $atomDate    = true;

        $adapted = $this->_adapter->adaptGloss($gloss, $languages, $inflections, $comments, $atomDate);

        $this->assertEquals(\stdClass::class, get_class($adapted));
        $this->assertEquals($gloss->created_at->toAtomString(), $adapted->created_at);
        $this->assertEquals($gloss->language->id, $adapted->language->id);
        $this->assertEquals($comments[$gloss->id], $adapted->comment_count);
        $this->assertEquals(implode(config('ed.gloss_translations_separator'), array_map(function ($t) {
            return $t->translation;
        }, $translations)), $adapted->all_translations);
    }

    public function testAdaptedGlossesAreEqualFromArrayAndRepository()
    {
        extract( $this->createGloss(__FUNCTION__) );

        $numberOfTranslations = count($translations);
        $numberOfDetails = count($details);
        $gloss0 = $this->getGlossRepository()->saveGloss('1', $sense, $gloss, $translations, $keywords, $details);
        
        $gloss = $gloss->replicate();
        $details = $this->createGlossDetails($gloss);
        $translations = $this->createTranslations();
        $gloss->external_id .= '-1';
        $gloss1 = $this->getGlossRepository()->saveGloss('2', $sense, $gloss, $translations, $keywords, $details);
        
        $gloss0->refresh();
        $gloss1->refresh();

        $gloss0->load('translations', 'gloss_details');
        $gloss1->load('translations', 'gloss_details');

        $glossesFromRepository = $this->getGlossRepository()->getGlossVersions([$gloss0->id, $gloss1->id])
            ->toArray();

        $searchWord      = $gloss1->word->word;
        $glosses         = [ $gloss0, $gloss1 ];
        $languages       = new Collection([ $gloss0->language ]);
        $inflections     = [];
        $comments        = [ $gloss0->id => 10, $gloss1->id => 20 ];
        $atomDate        = false;
        $groupByLanguage = true;

        // These are the adapted versions from the array
        $adapted = $this->_adapter->adaptGlosses($glosses, $inflections, $comments, $searchWord, $groupByLanguage, $atomDate);
        // These are the adapted version from the repository
        $adaptedFromRepository = $this->_adapter->adaptGlosses($glossesFromRepository, $inflections, $comments, $searchWord, 
            $groupByLanguage, $atomDate);
        
        $this->assertEquals(2, count($glosses));
        $this->assertEquals(count($glosses), count($glossesFromRepository) / ($numberOfTranslations * $numberOfDetails));
        $this->assertTrue($gloss0->is_latest == true);
        $this->assertTrue($gloss1->is_latest == true);
        $this->assertFalse($adapted['single']);

        $this->assertEquals(1, count($adapted['sections']));
        $this->assertEquals(2, count($adapted['sections'][0]['entities']));

        $this->assertEquals($gloss1->id, $adapted['sections'][0]['entities'][0]->id);
        $this->assertEquals($gloss0->id, $adapted['sections'][0]['entities'][1]->id);

        $this->assertEquals($comments[$gloss1->id], $adapted['sections'][0]['entities'][0]->comment_count);
        $this->assertEquals($comments[$gloss0->id], $adapted['sections'][0]['entities'][1]->comment_count);

        $this->assertEquals($adaptedFromRepository['sections'][0]['entities'][0]->id, $adapted['sections'][0]['entities'][0]->id);
        $this->assertEquals($adaptedFromRepository['sections'][0]['entities'][1]->id, $adapted['sections'][0]['entities'][1]->id);

        $this->assertEquals($adaptedFromRepository, $adapted);
    }

    public function testAdaptGlossesWithoutDetails()
    {
        extract( $this->createGloss(__FUNCTION__) );

        $gloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, []);

        $glossesFromRepository = $this->getGlossRepository()->getGlossVersions([$gloss->id])->all();
        $adapted = $this->_adapter->adaptGlosses($glossesFromRepository, [], [], $word);
        $adaptedGlossary = &$adapted['sections'][0]['entities'];

        $this->assertEquals(1, count($adaptedGlossary));
        $this->assertNotNull($adaptedGlossary[0]->gloss_details);
        $this->assertEquals(0, count($adaptedGlossary[0]->gloss_details));
    }

    public function testRating()
    {
        $glosses = [
            'mal', 'malina', 'malda', 'nan', 'tulca', 'anat'
        ];
        $expected = [
            'mal', 'malda', 'malina', 'nan', 'anat', 'tulca'
        ];
        $glossary = [];
        foreach ($glosses as $gloss) {
            extract( $this->createGloss(__FUNCTION__, $gloss) );
            $savedGloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
            $savedGloss->load('translations', 'gloss_details');
            $glossary[] = $savedGloss;
        }

        $adapted = $this->_adapter->adaptGlosses($glossary, [], [], 'mal');
        $adaptedGlossary = &$adapted['sections'][0]['entities'];

        for ($i = 0; $i < count($expected); $i += 1) {
            $this->assertEquals($expected[$i], $adaptedGlossary[$i]->word);
        }
    }

    public function testShouldGetVersions()
    {
        extract( $this->createGloss(__FUNCTION__) );
        $numberOfTranslations = count($translations);
        $numberOfDetails = count($details);

        $gloss0 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
        
        $gloss->is_uncertain = false;
        $translations = $this->createTranslations();
        $details = $this->createGlossDetails($gloss);
        $gloss1 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $newWord = $word.' 1';
        $translations = $this->createTranslations(); 
        $details = $this->createGlossDetails($gloss);
        $gloss2 = $this->getGlossRepository()->saveGloss($newWord, $sense, $gloss, $translations, $keywords, $details);

        $translations = array_merge(
            $this->createTranslations(),
            [ new Translation(['translation' => 'test '.count($translations)]) ]
        ); 
        $details = $this->createGlossDetails($gloss);
        $gloss3 = $this->getGlossRepository()->saveGloss($newWord, $sense, $gloss, $translations, $keywords, $details);

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
        $adapted = $this->_adapter->adaptGlosses($versions, [], [], $word, false, false);
        $glosses = & $adapted['sections'][0]['entities'];

        $this->assertEquals(4, count($glosses));

        $this->assertTrue(!!$glosses[0]->is_latest);
        $this->assertFalse(!!$glosses[1]->is_latest);
        $this->assertFalse(!!$glosses[2]->is_latest);
        $this->assertFalse(!!$glosses[3]->is_latest);

        $this->assertEquals($gloss3->id, $glosses[0]->id);
        $this->assertEquals($gloss2->id, $glosses[1]->id);
        $this->assertEquals($gloss1->id, $glosses[2]->id);
        $this->assertEquals($gloss0->id, $glosses[3]->id);
    }
}
