<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\Queue;
use Auth;
use DB;

use App\Repositories\MailSettingRepository;
use App\Models\Initialization\Morphs;
use App\Subscribers\DiscussMailEventSubscriber;
use App\Events\{
    ForumPostCreated
};
use App\Models\{
    Account,
    MailSetting,
    MailSettingOverride,

    Contribution,
    Language,
    ForumThread,
    ForumPost
};
use App\Mail\{
    ForumPostCreatedMail
};

class MailSettingRepositoryTest extends TestCase
{
    use Traits\MocksAuth {
        Traits\MocksAuth::setUp as setUpAuth;
    } // ;

    private $_accounts;
    private $_accountIds;

    public function setUp() 
    {
        parent::setUp();
        $this->setUpAuth();

        DB::beginTransaction();

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

    public function tearDown() 
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function testShouldQualify()
    {
        $account = $this->_accounts[0];

        $expected = [$account->email];
        $actual = $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution);
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
        $actual = $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution);
        $this->assertEquals($expected, $actual);
    }

    public function testShouldntQualifyDueToOverride()
    {
        $account = $this->_accounts[0];

        $this->_repository->setNotifications($account->id, $this->_contribution, false);
        
        $expected = [];
        $actual = $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution);
        $this->assertEquals($expected, $actual);

        MailSetting::create([
            'account_id' => $account->id,
            'forum_contribution_approved' => 1
        ]);
        $actual = $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution);
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
        $actual = $this->_repository->qualify([$account->id], 'forum_contribution_approved', $this->_contribution);
        
        $this->assertEquals($expected, $actual);
    }

    public function testShouldNotifyPostCreated()
    {
        $thread = ForumThread::create([
            'entity_type' => Morphs::getAlias($this->_contribution),
            'entity_id'   => $this->_contribution->id,
            'subject'     => 'Unit test',
            'account_id'  => $this->_accountIds[0]
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
        
        Queue::fake();
        
        resolve(DiscussMailEventSubscriber::class)->onForumPostCreated(new ForumPostCreated($post, $post->account_id));

        Queue::assertPushed(SendQueuedMailable::class, function ($job) use ($expected) {
            return array_map(function ($a) {
                return $a['address'];
            }, $job->mailable->to) === $expected;
        });
    }
}
