<?php

namespace Tests\Unit\Controllers;

use App\Models\Account;
use App\Security\RoleConstants;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SenseReviewControllerTest extends TestCase
{
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    public function test_the_page_is_closed_to_everyone_but_administrators()
    {
        $this->actingAs($this->account(RoleConstants::Users))
            ->get(route('sense-review.index'))
            ->assertForbidden();
    }

    public function test_an_administrator_is_shown_the_queue_with_the_reasons_senses_wait()
    {
        $response = $this->actingAs($this->account(RoleConstants::Administrators))
            ->get(route('sense-review.index'));

        $response->assertSuccessful();
        $response->assertSee('Senses awaiting review');
        $response->assertViewHas('waiting');
        $response->assertViewHas('reasons');
    }

    private function account(string $role): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne(['email_verified_at' => Carbon::now()]);
        // real accounts hold the default Users role as well, without which they are treated as banned
        $account->addMembershipTo(RoleConstants::Users);
        if ($role !== RoleConstants::Users) {
            $account->addMembershipTo($role);
        }

        return $account->refresh();
    }
}
