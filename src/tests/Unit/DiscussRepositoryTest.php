<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Auth;
use DB;

use Tests\Unit\Traits\CanCreateGloss;

use App\Repositories\DiscussRepository;
use App\Models\{
    ForumGroup,
    ForumThread
};

class DiscussRepositoryTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::tearDown as tearDownGlosses;
    } // ; <-- remedies Visual Studio Code colouring bug

    private $_repository;

    protected function setUp() 
    {
        parent::setUp();
        DB::beginTransaction();
        $this->setUpGlosses();

        $this->_repository = resolve(DiscussRepository::class);
    }

    protected function tearDown()
    {
        $this->tearDownGlosses();
        DB::rollBack();
        parent::tearDown();
    }

    public function testGetThreadForEntityShouldBeNull()
    {
        $thread = $this->_repository->getThreadForEntity('gloss', 1);
        $this->assertEquals($thread, null);
    }

    public function testGetThreadForEntityShouldBeNew()
    {
        extract( $this->createGloss(__FUNCTION__) );
        $gloss = $this->getRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $thread = $this->_repository->getThreadForEntity('gloss', $gloss->id, true);
        $this->assertTrue(is_array($thread));
        $this->assertTrue(isset($thread['thread']));

        $t = $thread['thread'];
        $this->assertTrue($t instanceof ForumThread);
        $this->assertEquals(
            $t->forum_group_id, 
            ForumGroup::where('role', 'gloss')->first()->id
        );
        $this->assertEquals(
            $t->id, 
            0
        );
    }
}
