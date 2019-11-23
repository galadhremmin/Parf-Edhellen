<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SystemError;
use App\Helpers\MarkdownParser;
use DB;

class UtilityApiTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();
    }

    public function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /* TODO: This test is incompatible with Laravel 5.6. CSRF token intercepts the request.
    public function testExpectsSuccessfulErrorLog()
    {
        $expected = [
            'message'  => 'unit test',
            'url'      => 'http://localhost.test/testErrorMessage',
            'category' => 'test'
        ];
        $response = $this->json('POST', '/api/v2/utility/error', $expected);
        $error = SystemError::orderBy('id', 'desc')->first();

        $this->assertEquals(201, $response->status());
        $this->assertEquals($expected['message'], $error->message);
        $this->assertEquals($expected['url'], $error->url);
        $this->assertEquals($expected['category'], $error->category);
    }
    */
}
