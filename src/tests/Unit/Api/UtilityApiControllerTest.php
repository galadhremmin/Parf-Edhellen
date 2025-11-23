<?php

namespace Tests\Unit\Api;

use App\Repositories\SystemErrorRepository;
use App\Interfaces\IMarkdownParser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UtilityApiControllerTest extends TestCase
{
    use DatabaseTransactions;

    private IMarkdownParser $_markdownParser;
    private SystemErrorRepository $_systemErrorRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_markdownParser = $this->createMock(IMarkdownParser::class);
        $this->_systemErrorRepository = $this->createMock(SystemErrorRepository::class);
    }

    public function test_logError_accepts_valid_category_with_lowercase_letters()
    {
        $this->_systemErrorRepository
            ->expects($this->once())
            ->method('saveFrontendException')
            ->with(
                'https://example.com/test',
                'Test error message',
                '',
                $this->stringContains('frontend-testcategory'),
                null
            );

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'testcategory',
        ]);

        $response->assertStatus(201);
    }

    public function test_logError_accepts_valid_category_with_numbers()
    {
        $this->_systemErrorRepository
            ->expects($this->once())
            ->method('saveFrontendException')
            ->with(
                'https://example.com/test',
                'Test error message',
                '',
                $this->stringContains('frontend-test123'),
                null
            );

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'test123',
        ]);

        $response->assertStatus(201);
    }

    public function test_logError_accepts_valid_category_with_letters_and_numbers()
    {
        $this->_systemErrorRepository
            ->expects($this->once())
            ->method('saveFrontendException')
            ->with(
                'https://example.com/test',
                'Test error message',
                '',
                $this->stringContains('frontend-cat123egory'),
                null
            );

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'cat123egory',
        ]);

        $response->assertStatus(201);
    }

    public function test_logError_rejects_category_with_uppercase_letters()
    {
        $this->_systemErrorRepository
            ->expects($this->never())
            ->method('saveFrontendException');

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'TestCategory',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category']);
    }

    public function test_logError_accepts_category_with_hyphens()
    {
        $this->_systemErrorRepository
            ->expects($this->once())
            ->method('saveFrontendException')
            ->with(
                'https://example.com/test',
                'Test error message',
                '',
                $this->stringContains('frontend-test-category'),
                null
            );

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'test-category',
        ]);

        $response->assertStatus(201);
    }

    public function test_logError_rejects_category_with_underscores()
    {
        $this->_systemErrorRepository
            ->expects($this->never())
            ->method('saveFrontendException');

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'test_category',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category']);
    }

    public function test_logError_rejects_category_with_spaces()
    {
        $this->_systemErrorRepository
            ->expects($this->never())
            ->method('saveFrontendException');

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'test category',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category']);
    }

    public function test_logError_rejects_category_with_special_characters()
    {
        $this->_systemErrorRepository
            ->expects($this->never())
            ->method('saveFrontendException');

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'test@category',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category']);
    }

    public function test_logError_rejects_category_with_unicode_characters()
    {
        $this->_systemErrorRepository
            ->expects($this->never())
            ->method('saveFrontendException');

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
            'category' => 'testé',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category']);
    }

    public function test_logError_rejects_category_with_sql_injection_attempts()
    {
        $this->_systemErrorRepository
            ->expects($this->never())
            ->method('saveFrontendException');

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $sqlInjectionAttempts = [
            "' OR '1'='1",
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM users --",
            "1' OR '1'='1",
            "admin'--",
            "' OR 1=1--",
            "') OR ('1'='1",
            "' OR 'a'='a",
            "' OR 1=1#",
            "admin'/*",
        ];

        foreach ($sqlInjectionAttempts as $attempt) {
            $response = $this->postJson('/api/v3/utility/error', [
                'message' => 'Test error message',
                'url' => 'https://example.com/test',
                'category' => $attempt,
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['category']);
        }
    }

    public function test_logError_works_without_category()
    {
        $this->_systemErrorRepository
            ->expects($this->once())
            ->method('saveFrontendException')
            ->with(
                'https://example.com/test',
                'Test error message',
                '',
                'frontend',
                null
            );

        $this->app->instance(IMarkdownParser::class, $this->_markdownParser);
        $this->app->instance(SystemErrorRepository::class, $this->_systemErrorRepository);

        $response = $this->postJson('/api/v3/utility/error', [
            'message' => 'Test error message',
            'url' => 'https://example.com/test',
        ]);

        $response->assertStatus(201);
    }
}

