<?php

namespace Tests\Unit\Repositories;

use App\Events\ContributionApproved;
use App\Events\ContributionRejected;
use App\Events\ForumPostCreated;
use App\Mail\ContributionApprovedMail;
use App\Mail\ContributionRejectedMail;
use App\Mail\ForumPostCreatedMail;
use App\Mail\ForumPostOnProfileMail;
use App\Models\Account;
use App\Models\Contribution;
use App\Models\ForumGroup;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\Initialization\Morphs;
use App\Models\Language;
use App\Models\MailSetting;
use App\Repositories\MailSettingRepository;
use App\Subscribers\ContributionMailEventSubscriber;
use App\Subscribers\DiscussMailEventSubscriber;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Mail;
use Tests\TestCase;
use Tests\Unit\Traits\MocksAuth;

class MailSettingRepositoryTest extends TestCase
{
    use DatabaseTransactions;
    use MocksAuth {
        MocksAuth::setUp as setUpAuth;
    } // ;

    private $_accounts;

    private $_accountIds;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuth();

        $this->_accounts = [
            Account::create([
                'nickname' => 'unit-test-1',
                'email' => 'unit-test-1@localhost.com',
                'identity' => 'unit-test-1',
            ]),
            Account::create([
                'nickname' => 'unit-test-2',
                'email' => 'unit-test-2@localhost.com',
                'identity' => 'unit-test-2',
            ]),
            Account::create([
                'nickname' => 'unit-test-3',
                'email' => 'unit-test-3@localhost.com',
                'identity' => 'unit-test-3',
            ]),
        ];
        $this->_accountIds = array_map(function ($a) {
            return $a->id;
        }, $this->_accounts);
        $this->_contribution = Contribution::create([
            'account_id' => $this->_accountIds[0],
            'language_id' => Language::first()->id,
            'word' => 'unit test',
            'payload' => json_encode([]),
            'type' => 'undefined',
        ]);
        $this->_repository = resolve(MailSettingRepository::class);
    }

    public function test_should_qualify()
    {
        $account = $this->_accounts[0];

        $expected = [$account->email];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );
        $this->assertEquals($expected, $actual);
    }

    public function test_shouldnt_qualify_due_to_setting()
    {
        $account = $this->_accounts[0];

        MailSetting::create([
            'account_id' => $account->id,
            'forum_contribution_approved' => 0,
        ]);

        $expected = [];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );
        $this->assertEquals($expected, $actual);
    }

    public function test_shouldnt_qualify_due_to_override()
    {
        $account = $this->_accounts[0];

        $this->_repository->setNotifications($account->id, $this->_contribution, false);

        $expected = [];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );
        $this->assertEquals($expected, $actual);

        MailSetting::create([
            'account_id' => $account->id,
            'forum_contribution_approved' => 1,
        ]);
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );
        $this->assertEquals($expected, $actual);
    }

    public function test_should_qualify_due_to_override()
    {
        $account = $this->_accounts[0];

        MailSetting::create([
            'account_id' => $account->id,
            'forum_contribution_approved' => 0,
        ]);
        $this->_repository->setNotifications($account->id, $this->_contribution, true);

        $expected = [$account->email];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );

        $this->assertEquals($expected, $actual);
    }

    public function test_shouldnt_qualify_due_to_malformed_email()
    {
        $account = $this->_accounts[0];
        $account->email = 'malformed-email';
        $account->save();

        $expected = [];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );

        $this->assertEquals($expected, $actual);
    }

    public function test_should_notify_post_created()
    {
        Mail::fake();
        Queue::fake();

        $thread = ForumThread::create([
            'entity_type' => Morphs::getAlias($this->_contribution),
            'entity_id' => $this->_contribution->id,
            'subject' => 'Unit test',
            'account_id' => $this->_accountIds[0],
            'forum_group_id' => ForumGroup::first()->id,
        ]);

        foreach ($this->_accountIds as $id) {
            $post = ForumPost::create([
                'forum_thread_id' => $thread->id,
                'account_id' => $id,
                'content' => 'Unit test '.$id,
            ]);
        }

        $expected = array_map(function ($a) {
            return $a->email;
        }, array_filter($this->_accounts, function ($a) use ($post) {
            return $a->id !== $post->account_id;
        })
        );

        resolve(DiscussMailEventSubscriber::class)->onForumPostCreated(new ForumPostCreated($post, $post->account_id));

        Mail::assertQueued(ForumPostCreatedMail::class, function ($job) use ($expected) {
            return count(array_filter($job->to, function ($a) use ($expected) {
                return in_array($a['address'], $expected);
            })) > 0;
        });
    }

    public function test_should_notify_post_on_profile()
    {
        Mail::fake();
        Queue::fake();

        $thread = ForumThread::create([
            'entity_type' => Morphs::getAlias($this->_accounts[0]),
            'entity_id' => $this->_accountIds[0],
            'subject' => 'Unit test',
            'account_id' => $this->_accountIds[1],
            'forum_group_id' => ForumGroup::first()->id,
        ]);

        $post = ForumPost::create([
            'forum_thread_id' => $thread->id,
            'account_id' => $this->_accountIds[1],
            'content' => 'Unit test',
        ]);

        $expected = [$this->_accounts[0]->email];

        resolve(DiscussMailEventSubscriber::class)->onForumPostCreated(new ForumPostCreated($post, $post->account_id));

        Mail::assertQueued(ForumPostOnProfileMail::class, function ($job) use ($expected) {
            return array_map(function ($a) {
                return $a['address'];
            }, $job->to) === $expected;
        });
    }

    public function test_should_notify_contributor_of_approval()
    {
        Mail::fake();
        Queue::fake();

        $contribution = $this->_contribution;
        $contribution->reviewed_by_account_id = $this->_accounts[1]->id;
        $contribution->is_approved = 1;
        $contribution->save();

        $expected = [$this->_accounts[0]->email];

        resolve(ContributionMailEventSubscriber::class)->onContributionApproved(new ContributionApproved($contribution));

        Mail::assertQueued(ContributionApprovedMail::class, function ($job) use ($expected) {
            return array_map(function ($a) {
                return $a['address'];
            }, $job->to) === $expected;
        });
    }

    public function test_should_notify_contributor_of_rejection()
    {
        Mail::fake();
        Queue::fake();

        $contribution = $this->_contribution;
        $contribution->reviewed_by_account_id = $this->_accounts[1]->id;
        $contribution->is_approved = 0;
        $contribution->save();

        $expected = [$this->_accounts[0]->email];

        resolve(ContributionMailEventSubscriber::class)->onContributionRejected(new ContributionRejected($contribution));

        Mail::assertQueued(ContributionRejectedMail::class, function ($job) use ($expected) {
            return array_map(function ($a) {
                return $a['address'];
            }, $job->to) === $expected;
        });
    }

    public function test_successful_cancellation_token()
    {
        $token = $this->_repository->generateCancellationToken($this->_accountIds[0], $this->_contribution);
        $this->assertNotNull($token);
        $this->assertNotEmpty($token);

        $expected = true;
        $actual = $this->_repository->handleCancellationToken($token);
        $this->assertEquals($expected, $actual);

        $expected = false;
        $actual = $this->_repository->canNotify($this->_accountIds[0], $this->_contribution);
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$this->_accountIds[0]], 'forum_contribution_approved', $this->_contribution)
        );
        $this->assertEquals($expected, $actual);
    }

    private function emailCollectionToArray($collection)
    {
        return $collection->pluck('email')->toArray();
    }
}
