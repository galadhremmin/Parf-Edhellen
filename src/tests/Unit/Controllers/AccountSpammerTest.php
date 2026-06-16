<?php

namespace Tests\Unit\Controllers;

use App\Models\Account;
use App\Models\AuditTrail;
use App\Models\ForumGroup;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\Initialization\Morphs;
use App\Repositories\AuditTrailRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Security\AccountManager;
use App\Security\RoleConstants;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccountSpammerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // The audit trail repository is a no-op in the console (test) context. Bind the concrete
        // implementation so we can assert that the account's activity is actually hidden.
        $this->app->bind(IAuditTrailRepository::class, AuditTrailRepository::class);
    }

    public function test_mark_as_spammer_revokes_roles_hides_activity_and_flags_account()
    {
        $admin = $this->makeAdmin();

        $spammer = Account::factory()->createOne();
        $spammer->addMembershipTo(RoleConstants::Users);
        $spammer->addMembershipTo(RoleConstants::Discuss);

        $auditTrail = $this->givenAuditTrail($spammer);

        $response = $this->actingAs($admin)
            ->post(route('account.mark-as-spammer', ['id' => $spammer->id]));

        $response->assertRedirect(route('account.edit', ['account' => $spammer->id]));

        $spammer->refresh();
        $this->assertTrue($spammer->is_spammer);
        $this->assertEquals(0, $spammer->roles()->count());

        $auditTrail->refresh();
        $this->assertEquals(1, $auditTrail->is_admin);
    }

    public function test_mark_as_spammer_hides_forum_posts()
    {
        $admin = $this->makeAdmin();

        $spammer = Account::factory()->createOne();
        $spammer->addMembershipTo(RoleConstants::Users);

        $post = $this->givenForumPost($spammer);

        $this->actingAs($admin)
            ->post(route('account.mark-as-spammer', ['id' => $spammer->id]))
            ->assertRedirect(route('account.edit', ['account' => $spammer->id]));

        $post->refresh();
        $this->assertEquals(1, $post->is_hidden);
    }

    public function test_mark_as_spammer_requires_administrator_role()
    {
        /** @var Account */
        $user = Account::factory()->createOne();
        $user->addMembershipTo(RoleConstants::Users);

        $spammer = Account::factory()->createOne();
        $spammer->addMembershipTo(RoleConstants::Users);

        $this->actingAs($user)
            ->post(route('account.mark-as-spammer', ['id' => $spammer->id]))
            ->assertForbidden();

        $spammer->refresh();
        $this->assertFalse($spammer->is_spammer);
    }

    public function test_non_root_admin_cannot_mark_another_administrator()
    {
        $admin = $this->makeAdmin();

        $otherAdmin = Account::factory()->createOne();
        $otherAdmin->addMembershipTo(RoleConstants::Administrators);

        $this->actingAs($admin)
            ->post(route('account.mark-as-spammer', ['id' => $otherAdmin->id]))
            ->assertStatus(400);

        $otherAdmin->refresh();
        $this->assertFalse($otherAdmin->is_spammer);
        $this->assertTrue($otherAdmin->memberOf(RoleConstants::Administrators));
    }

    public function test_spammer_is_blocked_from_the_site()
    {
        // Give the account the Users role so the 403 proves the is_spammer flag is what blocks it,
        // not the absence of a role.
        /** @var Account */
        $spammer = Account::factory()->createOne(['is_spammer' => true]);
        $spammer->addMembershipTo(RoleConstants::Users);

        $this->actingAs($spammer)
            ->get('/')
            ->assertForbidden();
    }

    public function test_spammer_accounts_cannot_be_linked()
    {
        /** @var Account */
        $master = Account::factory()->createOne(['is_master_account' => true]);
        /** @var Account */
        $spammer = Account::factory()->createOne(['is_spammer' => true]);

        $manager = resolve(AccountManager::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Accounts flagged as spammers cannot be linked.');

        $manager->linkAccountToMasterAccount($spammer, $master);
    }

    private function makeAdmin(): Account
    {
        /** @var Account */
        $admin = Account::factory()->createOne([
            'email_verified_at' => Carbon::now(),
        ]);
        // Real administrators also hold the default Users role; without it the InvalidUserGate
        // middleware treats the account as banned.
        $admin->addMembershipTo(RoleConstants::Users);
        $admin->addMembershipTo(RoleConstants::Administrators);

        return $admin;
    }

    private function givenAuditTrail(Account $account): AuditTrail
    {
        return AuditTrail::create([
            'account_id' => $account->id,
            'entity_id' => $account->id,
            'entity_type' => Morphs::getAlias(Account::class),
            'action_id' => AuditTrail::ACTION_COMMENT_ADD,
            'is_admin' => 0,
        ]);
    }

    private function givenForumPost(Account $account): ForumPost
    {
        $thread = ForumThread::create([
            'entity_type' => Morphs::getAlias(Account::class),
            'entity_id' => $account->id,
            'subject' => 'Unit test',
            'account_id' => $account->id,
            'forum_group_id' => ForumGroup::first()->id,
        ]);

        return ForumPost::create([
            'forum_thread_id' => $thread->id,
            'account_id' => $account->id,
            'content' => 'Spam content',
        ]);
    }
}
