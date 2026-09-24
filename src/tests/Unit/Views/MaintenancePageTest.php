<?php

namespace Tests\Unit\Views;

use Illuminate\Foundation\Exceptions\RegisterErrorViewPaths;
use Tests\TestCase;

class MaintenancePageTest extends TestCase
{
    public function test_the_page_names_a_word_and_says_what_is_happening()
    {
        $page = $this->render();

        $this->assertStringContainsString('Being rebound', $page);
        $this->assertStringContainsString('The dictionary is being rebound', $page);
        $this->assertStringContainsString('@parmaeldo', $page);
        $this->assertStringContainsString('https://x.com/parmaeldo', $page);

        // whichever word was drawn, its gloss and citation come with it
        $words = collect(config('ed-down.words'))->filter(
            fn (array $word) => str_contains($page, '<h1 lang="'.$word['language_tag'].'">'.$word['word'].'</h1>')
        );
        $this->assertCount(1, $words);
        $this->assertStringContainsString($words->first()['gloss'], $page);
        $this->assertStringContainsString('['.$words->first()['source'].']', $page);
    }

    public function test_it_announces_what_is_coming()
    {
        config(['ed-down.coming' => ['Senses grouped by meaning', 'Trails through the dictionary']]);

        $page = $this->render();

        $this->assertStringContainsString('New in this binding', $page);
        $this->assertStringContainsString('Senses grouped by meaning', $page);
        $this->assertStringContainsString('Trails through the dictionary', $page);
    }

    public function test_it_promises_nothing_when_nothing_is_configured()
    {
        config(['ed-down.coming' => []]);
        $this->assertStringNotContainsString('New in this binding', $this->render());

        config(['ed-down.coming' => null]);
        $this->assertStringNotContainsString('New in this binding', $this->render());
    }

    public function test_it_announces_three_at_most()
    {
        config(['ed-down.coming' => ['One', 'Two', 'Three', 'Four']]);

        $this->assertStringNotContainsString('Four', $this->render());
    }

    /**
     * The page as `artisan down --render` bakes it: no layout, and nothing of the site around it.
     */
    private function render(): string
    {
        // exactly how `artisan down --render` resolves and renders it
        (new RegisterErrorViewPaths)();
        $page = view('errors::503', ['retryAfter' => 60])->render();

        $this->assertStringNotContainsString('data-inject-module', $page);
        $this->assertStringNotContainsString('index.css', $page);

        return $page;
    }
}
