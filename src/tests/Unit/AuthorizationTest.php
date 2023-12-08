<?php

namespace Tests\Unit;

use App\Security\AccountManager;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function testAvailableNickname()
    {
        $expected = '5nOHivFbGMCVya8bY1SXeUZaXy';
        $actual   = resolve(AccountManager::class)->getNextAvailableNickname($expected);

        $this->assertEquals($expected, $actual);
    }

    public function testLongNickname()
    {
        $name = 'AReallyLongNicknameAndWeirdOnesLikeÅÄÖThatShouldBeLongerThan64CharactersAndWillBeShortenedIfTheCodeWorksProperly';
        $maxLength = config('ed.max_nickname_length');
        $this->assertTrue(mb_strlen($name) > $maxLength);

        $expected = mb_substr($name, 0, $maxLength - 4);
        $actual   = resolve(AccountManager::class)->getNextAvailableNickname($name);

        $this->assertEquals($expected, $actual);
    }
}
