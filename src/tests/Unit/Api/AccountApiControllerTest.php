<?php

namespace Tests\Unit\Api;

use Illuminate\Support\Str;
use Tests\TestCase;
use App\Http\Controllers\Api\v2\AccountApiController;
use App\Models\{
    Account,
    ForumGroup,
    ForumPost,
    ForumThread
};
use Exception;

class AccountApiControllerTest extends TestCase
{
    protected function setUp(): void
    {
        /**
         * This disables the exception handling to display the stacktrace on the console
         * the same way as it shown on the browser
         */
        parent::setUp();
        // $this->withoutExceptionHandling();
    }

    public function testDeletion()
    {
        $uuid = (string) Str::uuid();
        try {
            $account = Account::create([
                'nickname' => $uuid,
                'email'    => 'private@domain.com',
                'identity' => $uuid,
                'authorization_provider_id' => 1000,
                'profile'  => 'Lots of personal data.'
            ]);

            $response = $this->actingAs($account)
                ->delete(route('api.account.delete', ['id' => $account->id]));
            $response->assertRedirect(route('logout'));

            $deleted = Account::findOrFail($account->id);

            $this->assertTrue($deleted->nickname !== $account->nickname);
            $this->assertTrue($deleted->identity !== $account->identity);
            $this->assertTrue($deleted->profile !== $account->profile);

        } catch (Exception $ex) {
            throw $ex;
        } finally {
            $account->delete();
        }
    }

    public function testUnauthorizedToDelete()
    {
        $controller = resolve(AccountApiController::class);

        $uuid1 = (string) Str::uuid();
        $account1 = Account::create([
            'nickname' => $uuid1,
            'email'    => 'private1@domain.com',
            'identity' => $uuid1,
            'authorization_provider_id' => 1000,
            'profile'  => 'Lots of personal data.'
        ]);
        $uuid2 = (string) Str::uuid();
        $account2 = Account::create([
            'nickname' => $uuid2,
            'email'    => 'private2@domain.com',
            'identity' => $uuid2,
            'authorization_provider_id' => 2000,
            'profile'  => 'Lots of personal data.'
        ]);

        $path = route('api.account.delete', ['id' => $account1->id]);
        $this->delete($path)
            ->assertRedirect(route('login', ['redirect' => parse_url($path, PHP_URL_PATH)]));

        $this->actingAs($account2)
            ->delete($path)
            ->assertForbidden();
        
        $account1->delete();
        $account2->delete();
    }
}
