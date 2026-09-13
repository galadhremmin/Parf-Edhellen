<?php

namespace Tests\Unit\Controllers;

use App\Repositories\CrosswordRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

/**
 * The landing page had no coverage at all, which is uncomfortable for the one
 * route every visitor sees. These are deliberately shallow: they check the page
 * renders and that the pieces which can legitimately be absent -- a crossword
 * that has not been generated, a search figure with no traffic behind it -- are
 * absent gracefully rather than fatally.
 */
class HomeControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // These are populated by the controller through Cache::remember, so a
        // stale entry from another test (or a developer's browser) would make
        // these assertions meaningless.
        $this->forgetHomeCaches();
    }

    protected function tearDown(): void
    {
        $this->forgetHomeCaches();
        parent::tearDown();
    }

    private function forgetHomeCaches(): void
    {
        foreach (['ed.home.crosswords', 'ed.home.searches-per-day', 'ed.home.word-finder'] as $key) {
            Cache::forget($key);
        }
    }

    public function test_landing_page_renders()
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Every elvish word', false);
        $response->assertSee('A book of sources, not a list of words', false);
    }

    public function test_landing_page_shows_this_weeks_crossword_when_one_exists()
    {
        $crosswords = [[
            'language_id' => 1,
            'title' => 'Sindarin',
            'description' => null,
            'date' => '2026-03-22',
            'is_this_week' => true,
        ]];

        $repository = Mockery::mock(CrosswordRepository::class);
        $repository->shouldReceive('getCurrentPuzzles')->andReturn($crosswords);
        $this->app->instance(CrosswordRepository::class, $repository);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('This week&#039;s Sindarin puzzle', false);
        $response->assertSee(route('crossword.play', ['languageId' => 1, 'date' => '2026-03-22']), false);
    }

    public function test_landing_page_calls_an_older_puzzle_the_latest_rather_than_this_weeks()
    {
        // The generator can miss a week. Saying "this week's" about a fortnight-old
        // puzzle would be a small lie the reader can check.
        $crosswords = [[
            'language_id' => 1,
            'title' => 'Sindarin',
            'description' => null,
            'date' => '2026-03-08',
            'is_this_week' => false,
        ]];

        $repository = Mockery::mock(CrosswordRepository::class);
        $repository->shouldReceive('getCurrentPuzzles')->andReturn($crosswords);
        $this->app->instance(CrosswordRepository::class, $repository);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('The latest Sindarin puzzle', false);
        $response->assertDontSee('This week&#039;s Sindarin puzzle', false);
    }

    public function test_landing_page_survives_a_week_without_a_crossword()
    {
        // The generator command can fail or fall behind. When it does, the page
        // must fall back rather than link to a puzzle that would 404.
        $repository = Mockery::mock(CrosswordRepository::class);
        $repository->shouldReceive('getCurrentPuzzles')->andReturn([]);
        $this->app->instance(CrosswordRepository::class, $repository);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Sindarin puzzle', false);
        $response->assertSee('A puzzle a week', false);
        $response->assertSee(route('crossword.index'), false);
    }

    public function test_colophon_omits_the_search_figure_when_there_is_no_traffic()
    {
        Cache::put('ed.home.searches-per-day', 0, 60);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Fifteen years of dedication', false);
        $response->assertDontSee('searches a day', false);
    }

    public function test_colophon_shows_the_search_figure_when_there_is_traffic()
    {
        Cache::put('ed.home.searches-per-day', 214, 60);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('searches a day', false);
        $response->assertSee('214', false);
    }
}
