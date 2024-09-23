<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use Illuminate\Support\Facades\Queue;

use App\Models\{
    Account
};
use App\Repositories\AccountFeedRepository;

class AccountFeedRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * A basic example of versioning when saving glosses.
     *
     * @return void
     */
    public function testGenerateFeed()
    {
        $repository = resolve(AccountFeedRepository::class);
        $repository->generateForAccountId(4322);
    }
}
