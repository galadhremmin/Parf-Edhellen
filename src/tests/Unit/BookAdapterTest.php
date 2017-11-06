<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Auth;

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

    protected function setUp() 
    {
        parent::setUp();
        $this->setUpGlosses();
        
        $this->_adapter = resolve(BookAdapter::class);
    }

    protected function tearDown()
    {
        $this->tearDownGlosses();
        parent::tearDown();
    }

    public function testAdaptGloss()
    {
        extract( $this->createGloss(__FUNCTION__) );
        $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords);

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

    public function testAdaptGlosses()
    {
        extract( $this->createGloss(__FUNCTION__) );

        $numberOfTranslations = count($translations);
        $gloss0 = $this->getGlossRepository()->saveGloss('1', $sense, $gloss, $translations, $keywords);
        
        $gloss = $gloss->replicate();
        $translations = $this->createTranslations();
        $numberOfTranslations += count($translations);
        $gloss->external_id .= '-1';
        $gloss1 = $this->getGlossRepository()->saveGloss('2', $sense, $gloss, $translations, $keywords);
        
        $gloss0->refresh();
        $gloss1->refresh();

        $gloss0->load('translations');        
        $gloss1->load('translations');

        $glossesFromRepository = $this->getGlossRepository()->getGlosses([$gloss0->id, $gloss1->id])
            ->toArray();

        $searchWord      = $gloss1->word->word;
        $glosses         = [ $gloss0, $gloss1 ];
        $languages       = new Collection([ $gloss0->language ]);
        $inflections     = [];
        $comments        = [ $gloss0->id => 10, $gloss1->id => 20 ];
        $atomDate        = false;
        $groupByLanguage = true;

        $adapted = $this->_adapter->adaptGlosses($glosses, $inflections, $comments, $searchWord, $groupByLanguage, $atomDate);
        $adaptedFromRepository = $this->_adapter->adaptGlosses($glossesFromRepository, $inflections, $comments, $searchWord, 
            $groupByLanguage, $atomDate);

        $this->assertEquals(2, count($glosses));
        $this->assertEquals(count($glosses), count($glossesFromRepository) / $numberOfTranslations * count($glosses));
        $this->assertTrue($gloss0->is_latest == true);
        $this->assertTrue($gloss1->is_latest == true);
        $this->assertFalse($adapted['single']);

        $this->assertEquals(1, count($adapted['sections']));
        $this->assertEquals(2, count($adapted['sections'][0]['glosses']));

        $this->assertEquals($gloss1->id, $adapted['sections'][0]['glosses'][0]->id);
        $this->assertEquals($gloss0->id, $adapted['sections'][0]['glosses'][1]->id);

        $this->assertEquals($comments[$gloss1->id], $adapted['sections'][0]['glosses'][0]->comment_count);
        $this->assertEquals($comments[$gloss0->id], $adapted['sections'][0]['glosses'][1]->comment_count);

        $this->assertEquals($adaptedFromRepository['sections'][0]['glosses'][0]->id, $adapted['sections'][0]['glosses'][0]->id);
        $this->assertEquals($adaptedFromRepository['sections'][0]['glosses'][1]->id, $adapted['sections'][0]['glosses'][1]->id);

        $this->assertEquals($adaptedFromRepository, $adapted);
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
            $savedGloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords);
            $savedGloss->load('translations');
            $glossary[] = $savedGloss;
        }

        $adapted = $this->_adapter->adaptGlosses($glossary, [], [], 'mal');
        $adaptedGlossary = $adapted['sections'][0]['glosses'];

        for ($i = 0; $i < count($expected); $i += 1) {
            $this->assertEquals($expected[$i], $adaptedGlossary[$i]->word);
        }
    }
}
