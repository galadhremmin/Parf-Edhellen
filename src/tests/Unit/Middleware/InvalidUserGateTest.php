<?php

namespace Tests\Unit;

use App\Http\Middleware\InvalidUserGate;
use App\Models\Account;
use App\Models\Role;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvalidUserGateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_blocks_banned_users()
    {
        $uuid = (string) Str::uuid();
        $user = Account::create([
            'nickname' => $uuid,
            'email' => $uuid.'@unittest.com',
            'identity' => $uuid,
            'authorization_provider_id' => 1000,
            'profile' => 'Lots of personal data.',
            'is_deleted' => true,
        ]);

        $request = Request::create('/test-route', 'GET');
        $request->setUserResolver(fn () => $user);

        $gate = resolve(InvalidUserGate::class);
        $fn = fn () => 200;

        $response = $gate->handle($request, $fn);
        $this->assertEquals($response->getStatusCode(), 403);

        $user->is_deleted = false;
        $user->save();
        
        $response = $gate->handle($request, $fn);
        $this->assertEquals($response->getStatusCode(), 403);

        $user->addMembershipTo(RoleConstants::Users);

        $response = $gate->handle($request, $fn);
        $this->assertEquals($response, 200);

        $user->removeMembership(RoleConstants::Users);

        $response = $gate->handle($request, $fn);
        $this->assertEquals($response->getStatusCode(), 403);
    }
}
