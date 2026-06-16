<?php

namespace Tests\Unit\Repositories;

use App\Models\Account;
use App\Models\AuditTrail;
use App\Models\Initialization\Morphs;
use App\Repositories\AuditTrailRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuditTrailRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private AuditTrailRepository $_repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_repository = resolve(AuditTrailRepository::class);
    }

    public function test_public_only_hides_admin_entries_even_from_administrators()
    {
        /** @var Account */
        $admin = Account::factory()->createOne();
        $admin->addMembershipTo(RoleConstants::Administrators);
        $this->actingAs($admin);

        $visible = $this->givenAuditTrail($admin, isAdmin: false);
        $hidden = $this->givenAuditTrail($admin, isAdmin: true);

        $publicIds = $this->_repository->get(500, 0, [], true /* = publicOnly */)
            ->pluck('id');

        $this->assertTrue($publicIds->contains($visible->id));
        $this->assertFalse($publicIds->contains($hidden->id));
    }

    public function test_administrators_see_admin_entries_without_public_only()
    {
        /** @var Account */
        $admin = Account::factory()->createOne();
        $admin->addMembershipTo(RoleConstants::Administrators);
        $this->actingAs($admin);

        $hidden = $this->givenAuditTrail($admin, isAdmin: true);

        $ids = $this->_repository->get(500, 0, [], false /* = publicOnly */)
            ->pluck('id');

        $this->assertTrue($ids->contains($hidden->id));
    }

    public function test_hide_for_account_busts_the_front_page_cache()
    {
        /** @var Account */
        $account = Account::factory()->createOne();
        $this->givenAuditTrail($account, isAdmin: false);

        Cache::put(IAuditTrailRepository::HOME_CACHE_KEY, 'stale', 60);

        $this->_repository->hideForAccount($account);

        $this->assertFalse(Cache::has(IAuditTrailRepository::HOME_CACHE_KEY));
    }

    private function givenAuditTrail(Account $account, bool $isAdmin): AuditTrail
    {
        return AuditTrail::create([
            'account_id' => $account->id,
            'entity_id' => $account->id,
            'entity_type' => Morphs::getAlias(Account::class),
            'action_id' => AuditTrail::ACTION_COMMENT_ADD,
            'is_admin' => $isAdmin,
        ]);
    }
}
