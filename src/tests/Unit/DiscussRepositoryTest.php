<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Auth;

use App\Repositories\DiscussRepository;
use App\Models\{
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


        $thread = $this->_repository->getThreadForEntity('gloss', 1, true);
        $this->assertTrue($thread instanceof ForumThread);
    }
}
