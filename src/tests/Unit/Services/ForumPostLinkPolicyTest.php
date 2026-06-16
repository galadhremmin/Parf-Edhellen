<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Security\RoleConstants;
use App\Services\ForumPostLinkPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ForumPostLinkPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private ForumPostLinkPolicy $_policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_policy = resolve(ForumPostLinkPolicy::class);
    }

    #[DataProvider('linkProvider')]
    public function test_detects_links(string $content, bool $expected)
    {
        $this->assertSame($expected, $this->_policy->containsLink($content));
    }

    public static function linkProvider(): array
    {
        return [
            'http scheme' => ['Check out http://spam.example now', true],
            'https scheme' => ['Visit https://spam.example/page', true],
            'www prefix' => ['Visit www.spam-site.example today', true],
            'bare domain' => ['Buy here at free-stuff.shop right now', true],
            'markdown link' => ['Click [here](http://spam.example) please', true],
            'uppercase scheme' => ['HTTPS://SPAM.EXAMPLE', true],
            'plain text' => ['This is a normal sentence with no links.', false],
            'abbreviation' => ['Sindarin, i.e. the grey-elven tongue, e.g. mellon.', false],
            'version number' => ['We are on version 1.2 of the dictionary.', false],
            'empty' => ['', false],
        ];
    }

    public function test_null_content_contains_no_link()
    {
        $this->assertFalse($this->_policy->containsLink(null));
    }

    public function test_administrator_may_post_links()
    {
        $account = $this->makeAccount();
        $account->addMembershipTo(RoleConstants::Administrators);

        $this->assertTrue($this->_policy->mayPostLinks($account));
    }

    public function test_reviewer_may_post_links()
    {
        $account = $this->makeAccount();
        $account->addMembershipTo(RoleConstants::Reviewers);

        $this->assertTrue($this->_policy->mayPostLinks($account));
    }

    public function test_new_account_without_posts_may_not_post_links()
    {
        $account = $this->makeAccount(verifiedAt: Carbon::now()->subDays(30));

        $this->assertFalse($this->_policy->mayPostLinks($account));
    }

    public function test_recently_verified_account_may_not_post_links()
    {
        $account = $this->makeAccount(verifiedAt: Carbon::now()->subDay());
        $this->givePriorPost($account);

        $this->assertFalse($this->_policy->mayPostLinks($account));
    }

    public function test_unverified_account_may_not_post_links()
    {
        $account = $this->makeAccount(verifiedAt: null);
        $this->givePriorPost($account);

        $this->assertFalse($this->_policy->mayPostLinks($account));
    }

    public function test_established_account_may_post_links()
    {
        $account = $this->makeAccount(verifiedAt: Carbon::now()->subDays(30));
        $this->givePriorPost($account);

        $this->assertTrue($this->_policy->mayPostLinks($account));
    }

    private function makeAccount(?Carbon $verifiedAt = null): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne([
            'email_verified_at' => $verifiedAt,
        ]);
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    private function givePriorPost(Account $account): void
    {
        ForumPost::create([
            'account_id' => $account->id,
            'forum_thread_id' => ForumThread::first()->id,
            'content' => 'An earlier, harmless post.',
        ]);
    }
}
