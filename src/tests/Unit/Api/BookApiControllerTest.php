<?php

namespace Tests\Unit\Api;

use App\Models\LexicalEntry;
use App\Models\SearchKeyword;
use Tests\TestCase;

class BookApiControllerTest extends TestCase
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

    public function test_search_groups()
    {
        $response = $this->getJson(route('api.book.groups'));
        $response->assertSuccessful();
    }

    public function test_languages()
    {
        $response = $this->getJson(route('api.book.languages'));
        $response->assertSuccessful();
    }

    public function test_gloss()
    {
        $gloss = LexicalEntry::active()->first();
        $response = $this->getJson(route('api.book.gloss', ['glossId' => $gloss->id]));
        $response->assertSuccessful();
    }

    public function test_entities()
    {
        $response = $this->postJson(
            route('api.book.entities', ['groupId' => SearchKeyword::SEARCH_GROUP_DICTIONARY]),
            ['word' => 'a']
        );
        $response->assertSuccessful();
    }

    public function test_find()
    {
        $response = $this->postJson(route('api.book.find', []));
        $response->assertStatus(422);

        // TODO: Actually test translating a word
    }
}
