<?php

namespace Tests\Unit;

use App\Http\Middleware\RejectCrawlers;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RejectCrawlersTest extends TestCase
{
    public function test_passes_browsers()
    {
        $response = $this->handle('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36');

        $this->assertEquals(200, $response);
    }

    public function test_rejects_crawlers()
    {
        $userAgents = [
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/128.0.0.0 Safari/537.36',
            'python-requests/2.32.3',
            '',
            'Mozilla/5.0 '.str_repeat('a', RejectCrawlers::MAX_USER_AGENT_LENGTH),
        ];

        foreach ($userAgents as $userAgent) {
            try {
                $this->handle($userAgent);
                $this->fail('Crawler was let through: '.$userAgent);
            } catch (HttpException $ex) {
                $this->assertEquals(403, $ex->getStatusCode(), $userAgent);
            }
        }
    }

    public function test_rejects_crawlers_before_authentication()
    {
        $this->withHeader('User-Agent', 'python-requests/2.32.3')
            ->postJson('/api/v3/word-lists', [])
            ->assertStatus(403)
            ->assertJson(['message' => 'Crawlers and other automated clients are not permitted here.']);
    }

    public function test_explains_rejection_on_pages()
    {
        $this->withHeader('User-Agent', 'python-requests/2.32.3')
            ->get('/contribute/contribution')
            ->assertStatus(403)
            ->assertSee('Crawlers and other automated clients are not permitted here.');
    }

    private function handle(string $userAgent)
    {
        $request = Request::create('/test-route', 'POST', [], [], [], ['HTTP_USER_AGENT' => $userAgent]);

        return resolve(RejectCrawlers::class)->handle($request, fn () => 200);
    }
}
