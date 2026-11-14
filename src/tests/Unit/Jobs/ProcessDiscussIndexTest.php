<?php

namespace Tests\Unit\Jobs;

use App\Interfaces\IIdentifiesPhrases;
use App\Interfaces\IMarkdownParser;
use App\Interfaces\ISystemLanguageFactory;
use App\Jobs\ProcessDiscussIndex;
use App\Models\ForumPost;
use App\Models\Word;
use App\Repositories\SearchIndexRepository;
use App\Repositories\WordRepository;
use Mockery;
use Tests\TestCase;

class ProcessDiscussIndexTest extends TestCase
{
    public function test_indexes_cleaned_phrases_from_plain_text()
    {
        $post = $this->forumPost("*Athae**v**uig*\n\n> @quenya");

        $analyzer = Mockery::mock(IIdentifiesPhrases::class);
        $analyzer->shouldReceive('detectKeyPhrases')
            ->once()
            ->with(Mockery::on(fn (string $text) => ! str_contains($text, '*') && ! str_contains($text, '<')))
            ->andReturn(['athae**v**uig', "círan númenna**\n> @quenya", '**', 'Athaevuig']);

        $saved = [];
        $words = Mockery::mock(WordRepository::class);
        $words->shouldReceive('save')->andReturnUsing(function (string $phrase) use (&$saved) {
            $saved[] = $phrase;

            return new Word(['word' => $phrase]);
        });

        $index = Mockery::mock(SearchIndexRepository::class);
        $index->shouldReceive('deleteAll')->once()->with($post);
        $index->shouldReceive('createIndex')->times(3);

        $this->handle($post, $index, $words, $analyzer);

        $this->assertSame(['athaevuig', 'círan númenna quenya', 'Athaevuig'], $saved);
    }

    public function test_keeps_the_index_when_no_phrases_are_detected()
    {
        $post = $this->forumPost('Mae govannen');

        $analyzer = Mockery::mock(IIdentifiesPhrases::class);
        $analyzer->shouldReceive('detectKeyPhrases')->andReturn(['**', '> ']);

        $index = Mockery::mock(SearchIndexRepository::class);
        $index->shouldNotReceive('deleteAll');
        $index->shouldNotReceive('createIndex');

        $this->handle($post, $index, Mockery::mock(WordRepository::class), $analyzer);
    }

    private function forumPost(string $content): ForumPost
    {
        $post = new ForumPost(['content' => $content, 'account_id' => 1]);
        $post->id = 1;
        $post->is_deleted = false;

        return $post;
    }

    private function handle(ForumPost $post, SearchIndexRepository $index, WordRepository $words, IIdentifiesPhrases $analyzer): void
    {
        $languages = Mockery::mock(ISystemLanguageFactory::class);
        $languages->shouldReceive('language')->andReturn(null);

        (new ProcessDiscussIndex($post))->handle($index, $words, $analyzer, $languages, $this->app->make(IMarkdownParser::class));
    }
}
