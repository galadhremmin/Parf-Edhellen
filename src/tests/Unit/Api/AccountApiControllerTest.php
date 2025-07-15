<?php

namespace Tests\Unit\Api;

use App\Helpers\StorageHelper;
use App\Models\Account;
use App\Security\RoleConstants;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountApiControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_edited()
    {
        $uuid = (string) Str::uuid();
        try {
            $account = Account::create([
                'nickname' => $uuid,
                'email' => 'private@domain.com',
                'identity' => $uuid,
                'authorization_provider_id' => 1000,
                'profile' => 'Lots of personal data.',
            ]);

            $account->addMembershipTo(RoleConstants::Users);

            $newNickname = (string) Str::uuid();
            $newIntroduction = 'No personal data';

            $response = $this->actingAs($account)
                ->post(route('api.account.update', ['id' => $account->id]), [
                    'nickname' => $newNickname,
                    'tengwar' => $newNickname,
                    'introduction' => $newIntroduction,
                ]);
            $response->assertSuccessful();
            $account->refresh();

            $this->assertEquals($newNickname, $account->nickname);
            $this->assertEquals($newNickname, $account->tengwar);
            $this->assertEquals($newIntroduction, $account->profile);

        } catch (Exception $ex) {
            throw $ex;
        } finally {
            $account->delete();
        }
    }

    public function test_avatar()
    {
        $uuid = (string) Str::uuid();
        $avatarPath = null;
        try {
            $account = Account::create([
                'nickname' => $uuid,
                'email' => 'private@domain.com',
                'identity' => $uuid,
                'authorization_provider_id' => 1000,
                'profile' => 'Lots of personal data.',
            ]);

            $account->addMembershipTo(RoleConstants::Users);

            $newAvatar = UploadedFile::fake()->image('avatar.gif', 200, 200);

            $response = $this->actingAs($account)
                ->post(route('api.account.update-avatar', ['id' => $account->id]), [
                    'avatar' => $newAvatar,
                ]);
            $response->assertSuccessful();
            $account->refresh();

            $helper = new StorageHelper;
            $avatarPath = $helper->accountAvatar($account, false);
            $this->assertNotNull($avatarPath);

        } catch (Exception $ex) {
            throw $ex;
        } finally {
            $account->delete();
            if ($avatarPath !== null) {
                unlink(public_path().$avatarPath);
            }
        }
    }

    public function test_deletion()
    {
        $uuid = (string) Str::uuid();
        try {
            $account = Account::create([
                'nickname' => $uuid,
                'email' => 'private@domain.com',
                'identity' => $uuid,
                'authorization_provider_id' => 1000,
                'profile' => 'Lots of personal data.',
            ]);

            $account->addMembershipTo(RoleConstants::Users);

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

    public function test_unauthorized_to_delete()
    {
        $uuid1 = (string) Str::uuid();
        $account1 = Account::create([
            'nickname' => $uuid1,
            'email' => 'private1@domain.com',
            'identity' => $uuid1,
            'authorization_provider_id' => 1000,
            'profile' => 'Lots of personal data.',
        ]);
        $uuid2 = (string) Str::uuid();
        $account2 = Account::create([
            'nickname' => $uuid2,
            'email' => 'private2@domain.com',
            'identity' => $uuid2,
            'authorization_provider_id' => 2000,
            'profile' => 'Lots of personal data.',
        ]);

        $account1->addMembershipTo(RoleConstants::Users);
        $account2->addMembershipTo(RoleConstants::Users);

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
