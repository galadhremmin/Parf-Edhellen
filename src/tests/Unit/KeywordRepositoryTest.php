<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Auth;
use Queue;

use Tests\Unit\Traits\CanCreateGloss;
use App\Repositories\KeywordRepository;
use App\Models\{
    Gloss,
    Translation
};

class KeywordRepositoryTest extends TestCase
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
    public function testResolveKeyword()
    {
        extract( $this->createGloss(__FUNCTION__) );

        // Create an origin gloss, to validate the versioning system. By appending 'origin' to the word string,
        // the next gloss saved (with an unsuffixed word) create a new version of the gloss.
        $keywords[] = $word;
        $gloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
        
        $repository = resolve(KeywordRepository::class);
        
        $repository->createKeyword($gloss->word, $gloss->sense, $gloss);
        $repository->createKeyword($gloss->word, $gloss->sense, $gloss);

        $expected = array_unique(array_merge($keywords, array_map(function ($t) {
            return $t->translation;
        }, $translations), [ $word, $gloss->sense->word->word ]));
        $actual = $gloss->keywords->map(function ($k) {
            return $k->keyword;
        })->toArray();

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }
}
