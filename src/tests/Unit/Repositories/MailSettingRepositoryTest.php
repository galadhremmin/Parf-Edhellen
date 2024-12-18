<?php
namespace Tests\Unit\Repositories;

use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Auth;
use DB;
use Mail;

use Tests\Unit\Traits\MocksAuth;

use App\Repositories\MailSettingRepository;
use App\Models\Initialization\Morphs;
use App\Subscribers\{
    ContributionMailEventSubscriber,
    DiscussMailEventSubscriber
};
use App\Events\{
    ContributionApproved,
    ContributionRejected,
    ForumPostCreated
};
use App\Models\{
    Account,
    MailSetting,

    Contribution,
    Language,
    ForumThread,
    ForumGroup,
    ForumPost
};
use App\Mail\{
    ContributionApprovedMail,
    ContributionRejectedMail,
    ForumPostCreatedMail,
    ForumPostOnProfileMail
};
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MailSettingRepositoryTest extends TestCase
{
    use DatabaseTransactions; 

    use MocksAuth {
        MocksAuth::setUp as setUpAuth;
    } // ;

    private $_accounts;
    private $_accountIds;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpAuth();

        $this->_accounts = [
            Account::create([
                'nickname' => 'unit-test-1',
                'email'    => 'unit-test-1@localhost.com',
                'identity' => 'unit-test-1'
            ]),
            Account::create([
                'nickname' => 'unit-test-2',
                'email'    => 'unit-test-2@localhost.com',
                'identity' => 'unit-test-2'
            ]),
            Account::create([
                'nickname' => 'unit-test-3',
                'email'    => 'unit-test-3@localhost.com',
                'identity' => 'unit-test-3'
            ])
        ];
        $this->_accountIds = array_map(function ($a) {
            return $a->id;
        }, $this->_accounts);
        $this->_contribution = Contribution::create([
            'account_id'   => $this->_accountIds[0],
            'language_id'  => Language::first()->id,
            'word'         => 'unit test',
            'payload'      => json_encode([]),
            'type'         => 'undefined' 
        ]);
        $this->_repository = resolve(MailSettingRepository::class);
    }
    
    public function testShouldQualify()
    {
        $account = $this->_accounts[0];

        $expected = [$account->email];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );
        $this->assertEquals($expected, $actual);
    }

    public function testShouldntQualifyDueToSetting()
    {
        $account = $this->_accounts[0];

        MailSetting::create([
            'account_id' => $account->id,
            'forum_contribution_approved' => 0
        ]);
        
        $expected = [];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );
        $this->assertEquals($expected, $actual);
    }

    public function testShouldntQualifyDueToOverride()
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
            'forum_contribution_approved' => 1
        ]);
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );
        $this->assertEquals($expected, $actual);
    }

    public function testShouldQualifyDueToOverride()
    {
        $account = $this->_accounts[0];

        MailSetting::create([
            'account_id' => $account->id,
            'forum_contribution_approved' => 0
        ]);
        $this->_repository->setNotifications($account->id, $this->_contribution, true);
        
        $expected = [$account->email];
        $actual = $this->emailCollectionToArray(
            $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution)
        );
        
        $this->assertEquals($expected, $actual);
    }

    public function testShouldntQualifyDueToMalformedEmail()
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

    public function testShouldNotifyPostCreated()
    {
        Mail::fake();
        Queue::fake();

        $thread = ForumThread::create([
            'entity_type'    => Morphs::getAlias($this->_contribution),
            'entity_id'      => $this->_contribution->id,
            'subject'        => 'Unit test',
            'account_id'     => $this->_accountIds[0],
            'forum_group_id' => ForumGroup::first()->id
        ]);

        foreach ($this->_accountIds as $id) {
            $post = ForumPost::create([
                'forum_thread_id' => $thread->id,
                'account_id'      => $id,
                'content'         => 'Unit test '.$id
            ]);
        }

        $expected = array_map(function ($a) {
                return $a->email;
            }, array_filter($this->_accounts, function($a) use($post) { 
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

    public function testShouldNotifyPostOnProfile()
    {
        Mail::fake();
        Queue::fake();

        $thread = ForumThread::create([
            'entity_type'    => Morphs::getAlias($this->_accounts[0]),
            'entity_id'      => $this->_accountIds[0],
            'subject'        => 'Unit test',
            'account_id'     => $this->_accountIds[1],
            'forum_group_id' => ForumGroup::first()->id
        ]);

        $post = ForumPost::create([
            'forum_thread_id' => $thread->id,
            'account_id'      => $this->_accountIds[1],
            'content'         => 'Unit test'
        ]);
        
        $expected = [$this->_accounts[0]->email];
        
        resolve(DiscussMailEventSubscriber::class)->onForumPostCreated(new ForumPostCreated($post, $post->account_id));

        Mail::assertQueued(ForumPostOnProfileMail::class, function ($job) use ($expected) {
            return array_map(function ($a) {
                return $a['address'];
            }, $job->to) === $expected;
        });
    }

    public function testShouldNotifyContributorOfApproval()
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

    public function testShouldNotifyContributorOfRejection()
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

    public function testSuccessfulCancellationToken()
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
