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

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private int $_providerCounter = 0;

    private function makeProvider(): AuthorizationProvider
    {
        $suffix = 'merge-'.($this->_providerCounter++).'-'.Str::random(6);

        return AuthorizationProvider::create([
            'name' => "Test provider $suffix",
            'name_identifier' => $suffix,
            'logo_file_name' => "$suffix.jpg",
        ]);
    }

    private function makeAccount(string $email, bool $verified = false): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne([
            'authorization_provider_id' => $this->makeProvider()->id,
            'email' => $email,
            'email_verified_at' => $verified ? Carbon::now() : null,
        ]);
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    private function makeMergeRequest(Account $owner, array $accountIds, array $overrides = []): AccountMergeRequest
    {
        return AccountMergeRequest::create(array_merge([
            'id' => Str::uuid(),
            'account_id' => $owner->id,
            'account_ids' => json_encode($accountIds),
            'verification_token' => 'test-token-'.Str::random(16),
            'requester_account_id' => $owner->id,
            'requester_ip' => '127.0.0.1',
            'is_fulfilled' => false,
            'is_error' => false,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // merge() — input validation
    // -------------------------------------------------------------------------

    public function test_merge_rejects_non_numeric_account_id()
    {
        $account = $this->makeAccount('user@example.com', verified: true);
        Mail::fake();

        $this->actingAs($account)
            ->post(route('account.merge'), ['account_id' => ['not-a-number']])
            ->assertSessionHasErrors('account_id');

        $this->assertDatabaseMissing('account_merge_requests', ['account_id' => $account->id]);
    }

    public function test_merge_rejects_restricted_account_id()
    {
        $account = $this->makeAccount('restricted@example.com', verified: true);
        $target  = $this->makeAccount('restricted@example.com', verified: true);

        config(['ed.restricted_profile_ids' => [$target->id]]);
        Mail::fake();

        $this->actingAs($account)
            ->post(route('account.merge'), ['account_id' => [$account->id, $target->id]])
            ->assertSessionHasErrors('account_id');

        $this->assertDatabaseMissing('account_merge_requests', ['account_id' => $account->id]);
    }

    public function test_merge_rejects_when_only_own_account_submitted()
    {
        $account = $this->makeAccount('solo@example.com', verified: true);
        Mail::fake();

        $this->actingAs($account)
            ->post(route('account.merge'), ['account_id' => [$account->id]])
            ->assertSessionHasErrors('account_id');

        $this->assertDatabaseMissing('account_merge_requests', ['account_id' => $account->id]);
    }

    public function test_merge_rejects_already_linked_account()
    {
        $email   = 'linked@example.com';
        $account = $this->makeAccount($email, verified: true);
        $target  = $this->makeAccount($email);

        // Simulate $target already being linked to a master account
        $master = $this->makeAccount($email, verified: true);
        $target->update(['master_account_id' => $master->id]);

        Mail::fake();

        $this->actingAs($account)
            ->post(route('account.merge'), ['account_id' => [$account->id, $target->id]])
            ->assertSessionHasErrors('account_id');

        $this->assertDatabaseMissing('account_merge_requests', ['account_id' => $account->id]);
    }

    public function test_merge_rejects_when_unfulfilled_request_already_exists()
    {
        $email   = 'pending@example.com';
        $account = $this->makeAccount($email, verified: true);
        $other   = $this->makeAccount($email);

        $this->makeMergeRequest($account, [$account->id, $other->id]);
        Mail::fake();

        $this->actingAs($account)
            ->post(route('account.merge'), ['account_id' => [$account->id, $other->id]])
            ->assertSessionHasErrors('account_id');
    }

    public function test_merge_rejects_unverified_email()
    {
        $email   = 'unverified@example.com';
        $account = $this->makeAccount($email, verified: false);
        $other   = $this->makeAccount($email);

        Mail::fake();

        $this->actingAs($account)
            ->post(route('account.merge'), ['account_id' => [$account->id, $other->id]])
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseMissing('account_merge_requests', ['account_id' => $account->id]);
    }

    public function test_merge_rejects_accounts_with_different_email()
    {
        $account = $this->makeAccount('owner@example.com', verified: true);
        $victim  = $this->makeAccount('victim@example.com');

        Mail::fake();

        $this->actingAs($account)
            ->post(route('account.merge'), ['account_id' => [$account->id, $victim->id]])
            ->assertSessionHasErrors('account_id');

        $this->assertDatabaseMissing('account_merge_requests', ['account_id' => $account->id]);
    }

    // -------------------------------------------------------------------------
    // mergeStatus()
    // -------------------------------------------------------------------------

    public function test_merge_status_accessible_by_request_owner()
    {
        $account = $this->makeAccount('owner@example.com', verified: true);
        $other   = $this->makeAccount('owner@example.com');
        $mr      = $this->makeMergeRequest($account, [$account->id, $other->id]);

        $this->actingAs($account)
            ->get(route('account.merge-status', ['requestId' => $mr->id]))
            ->assertOk()
            ->assertViewIs('account.merge-status');
    }

    public function test_merge_status_accessible_by_account_listed_in_request()
    {
        $owner = $this->makeAccount('listed@example.com', verified: true);
        $other = $this->makeAccount('listed@example.com', verified: true);
        $mr    = $this->makeMergeRequest($owner, [$owner->id, $other->id]);

        $this->actingAs($other)
            ->get(route('account.merge-status', ['requestId' => $mr->id]))
            ->assertOk()
            ->assertViewIs('account.merge-status');
    }

    public function test_merge_status_rejects_unrelated_user()
    {
        $owner     = $this->makeAccount('owner2@example.com', verified: true);
        $other     = $this->makeAccount('owner2@example.com', verified: true);
        $unrelated = $this->makeAccount('stranger@example.com', verified: true);
        $mr        = $this->makeMergeRequest($owner, [$owner->id, $other->id]);

        $this->actingAs($unrelated)
            ->get(route('account.merge-status', ['requestId' => $mr->id]))
            ->assertStatus(404);
    }

    public function test_merge_status_returns_404_for_unknown_request()
    {
        $account = $this->makeAccount('ghost@example.com', verified: true);

        $this->actingAs($account)
            ->get(route('account.merge-status', ['requestId' => Str::uuid()]))
            ->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // cancelMerge()
    // -------------------------------------------------------------------------

    public function test_cancel_merge_removes_unfulfilled_request()
    {
        $account = $this->makeAccount('cancel@example.com', verified: true);
        $other   = $this->makeAccount('cancel@example.com');
        $mr      = $this->makeMergeRequest($account, [$account->id, $other->id]);

        $this->actingAs($account)
            ->post(route('account.cancel-merge', ['requestId' => $mr->id]))
            ->assertRedirect(route('account.security'));

        $this->assertDatabaseMissing('account_merge_requests', ['id' => $mr->id]);
    }

    public function test_cancel_merge_rejects_another_users_request()
    {
        $owner     = $this->makeAccount('cancel-owner@example.com', verified: true);
        $other     = $this->makeAccount('cancel-owner@example.com', verified: true);
        $unrelated = $this->makeAccount('cancel-stranger@example.com', verified: true);
        $mr        = $this->makeMergeRequest($owner, [$owner->id, $other->id]);

        $this->actingAs($unrelated)
            ->post(route('account.cancel-merge', ['requestId' => $mr->id]))
            ->assertStatus(404);

        $this->assertDatabaseHas('account_merge_requests', ['id' => $mr->id]);
    }

    public function test_cancel_merge_does_not_delete_fulfilled_request()
    {
        $account = $this->makeAccount('cancel-fulfilled@example.com', verified: true);
        $other   = $this->makeAccount('cancel-fulfilled@example.com');
        $mr      = $this->makeMergeRequest($account, [$account->id, $other->id], ['is_fulfilled' => true]);

        $this->actingAs($account)
            ->post(route('account.cancel-merge', ['requestId' => $mr->id]))
            ->assertRedirect(route('account.security'));

        $this->assertDatabaseHas('account_merge_requests', ['id' => $mr->id]);
    }

    // -------------------------------------------------------------------------
    // confirmMerge()
    // -------------------------------------------------------------------------

    public function test_confirm_merge_rejects_wrong_token()
    {
        $account = $this->makeAccount('confirm@example.com', verified: true);
        $other   = $this->makeAccount('confirm@example.com');
        $this->makeMergeRequest($account, [$account->id, $other->id], [
            'verification_token' => 'correct-token',
        ]);

        $this->actingAs($account)
            ->get(route('account.confirm-merge', ['requestId' => Str::uuid(), 'token' => 'wrong-token']))
            ->assertSessionHasErrors('token');
    }

    public function test_confirm_merge_rejects_already_fulfilled_token()
    {
        $account = $this->makeAccount('fulfilled@example.com', verified: true);
        $other   = $this->makeAccount('fulfilled@example.com');
        $this->makeMergeRequest($account, [$account->id, $other->id], [
            'verification_token' => 'used-token',
            'is_fulfilled' => true,
        ]);

        $this->actingAs($account)
            ->get(route('account.confirm-merge', ['requestId' => Str::uuid(), 'token' => 'used-token']))
            ->assertSessionHasErrors('token');
    }

    public function test_confirm_merge_rejects_unverified_email()
    {
        // The 'verified' middleware on this route group enforces email verification
        // before the controller is reached, so an unverified account is redirected.
        $email   = 'unverified-confirm@example.com';
        $account = $this->makeAccount($email, verified: false);
        $other   = $this->makeAccount($email);
        $token   = 'valid-token-'.Str::random(16);
        $this->makeMergeRequest($account, [$account->id, $other->id], [
            'verification_token' => $token,
        ]);

        $this->actingAs($account)
            ->get(route('account.confirm-merge', ['requestId' => Str::uuid(), 'token' => $token]))
            ->assertRedirect(route('verification.notice'));
    }

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function test_merge_accounts()
    {
        $email    = 'merge-test-shared@example.com';
        $account1 = $this->makeAccount($email);
        $account2 = $this->makeAccount($email);

        $mail = Mail::fake();

        $this->actingAs($account1)
            ->post(route('account.merge'), ['account_id' => [$account1->id, $account2->id]])
            ->assertRedirect(route('verification.notice'));

        $account1->update(['email_verified_at' => Carbon::now()]);

        $this->actingAs($account1)
            ->post(route('account.merge'), ['account_id' => [$account1->id, $account2->id]])
            ->assertRedirect();

        $merger = AccountMergeRequest::where('account_id', $account1->id)->first();
        $this->assertNotNull($merger);

        $mail->assertQueued(AccountMergeMail::class);

        $this->actingAs($account1)
            ->get(route('account.confirm-merge', ['requestId' => $merger->id, 'token' => $merger->verification_token]))
            ->assertRedirect(route('account.merge-status', ['requestId' => $merger->id]));

        $account1->refresh();
        $account2->refresh();

        foreach ([$account1, $account2] as $account) {
            $this->assertNotNull($account->master_account);
            $this->assertTrue(! $account->is_master_account);
        }

        $this->assertTrue(! $account1->master_account->is_passworded);
    }
}
