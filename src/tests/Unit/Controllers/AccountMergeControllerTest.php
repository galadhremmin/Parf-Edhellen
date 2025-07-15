<?php

namespace Tests\Unit\Controllers;

use App\Mail\AccountMergeMail;
use App\Models\Account;
use App\Models\AccountMergeRequest;
use App\Models\AuthorizationProvider;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountMergeControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_merge_accounts()
    {
        $providers = [
            AuthorizationProvider::create([
                'name' => 'Merge test provider 1',
                'name_identifier' => 'unit-test-merge-1',
                'logo_file_name' => 'unit-test-merge-1.jpg',
            ]),
            AuthorizationProvider::create([
                'name' => 'Merge test provider 2',
                'name_identifier' => 'unit-test-merge-2',
                'logo_file_name' => 'unit-test-merge-2.jpg',
            ]),
        ];

        $uuid1 = (string) Str::uuid();
        $account1 = Account::create([
            'nickname' => $uuid1,
            'email' => 'private1@domain.com',
            'identity' => $uuid1,
            'authorization_provider_id' => $providers[0]->id,
            'profile' => 'Lots of personal data.',
        ]);
        $uuid2 = (string) Str::uuid();
        $account2 = Account::create([
            'nickname' => $uuid2,
            'email' => 'private1@domain.com',
            'identity' => $uuid2,
            'authorization_provider_id' => $providers[1]->id,
            'profile' => 'Lots of personal data.',
        ]);

        $account1->addMembershipTo(RoleConstants::Users);
        $account2->addMembershipTo(RoleConstants::Users);

        $mail = Mail::fake();

        $request = $this->actingAs($account1)
            ->post(route('account.merge'), [
                'account_id' => [$account1->id, $account2->id],
            ])
            ->assertRedirect(route('verification.notice'));

        $account1->update([
            'email_verified_at' => Carbon::now(),
        ]);

        $request = $this->actingAs($account1)
            ->post(route('account.merge'), [
                'account_id' => [$account1->id, $account2->id],
            ])
            ->assertRedirect();

        $merger = AccountMergeRequest::where('account_id', $account1->id)->first();
        $this->assertNotNull($merger);
        $requestId = $merger->id;
        $verificationToken = $merger->verification_token;

        $request->assertRedirect(route('account.merge-status', ['requestId' => $requestId]));
        $mail->assertQueued(AccountMergeMail::class);

        $request = $this->actingAs($account1)
            ->get(route('account.confirm-merge', ['requestId' => $requestId, 'token' => $verificationToken]))
            ->assertRedirect(route('account.merge-status', ['requestId' => $requestId]));

        $account1->refresh();
        $account2->refresh();

        foreach ([$account1, $account2] as $account) {
            $this->assertNotNull($account->master_account);
            $this->assertTrue(! $account->is_master_account);
        }

        $this->assertTrue(! $account1->master_account->is_passworded);
    }
}
