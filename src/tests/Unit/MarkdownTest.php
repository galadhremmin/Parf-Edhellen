<?php

namespace Tests\Unit;

use App\Helpers\MarkdownParser;
use App\Interfaces\IExternalToInternalUrlResolver;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    public function test_long_uri_implicit_shortening()
    {
        $uri = 'https://www.reallylonglink.com/a/path/to/a/resource?a=ridiculously+long+query+string&with+multiple+parameters=true';
        $expected = '<p><a href="'.htmlentities($uri).'" title="Goes to: '.htmlentities($uri).'">www.reallylonglink.com</a></p>';

        $parser = new MarkdownParser;
        $actual = $parser->text($uri);

        $this->assertEquals($expected, $actual);
    }

    public function test_long_uri_explicit_shortening()
    {
        $uri = 'https://www.reallylonglink.com/a/path/to/a/resource?a=ridiculously+long+query+string&with+multiple+parameters=true';
        $expected = '<p><a href="'.htmlentities($uri).'" title="Goes to: '.htmlentities($uri).'">www.reallylonglink.com</a></p>';

        $parser = new MarkdownParser;
        $actual = $parser->text('['.$uri.']('.$uri.')');

        $this->assertEquals($expected, $actual);
    }

    public function test_uri_with_descrition()
    {
        $uri = 'https://www.reallylonglink.com/a/path/to/a/resource?a=ridiculously+long+query+string&with+multiple+parameters=true';

        $markdown = '[A text sample]('.$uri.')';
        $expected = '<p><a href="'.htmlentities($uri).'">A text sample</a></p>';

        $parser = new MarkdownParser;
        $actual = $parser->text($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function test_tengwar_transcription()
    {
        $markdown = 'mae govannen @sindarin:mellon@!';
        $expected = '<p>mae govannen <span class="tengwar" data-tengwar-transcribe="true" data-tengwar-mode="sindarin">mellon</span>!</p>';

        $parser = new MarkdownParser;
        $actual = $parser->text($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function test_reference_with_short_language_name()
    {
        $markdown = 'mae govannen [[s:mellon]]!';
        $expected = '<p>mae govannen <a href="/w/mellon/s" title="Navigate to mellon." class="ed-word-reference" data-word="mellon" data-original-word="mellon" data-language-short-name="s">mellon</a>!</p>';

        $parser = new MarkdownParser;
        $actual = $parser->text($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function test_reference()
    {
        $markdown = 'mae govannen [[mellon]]!';
        $expected = '<p>mae govannen <a href="/w/mellon" title="Navigate to mellon." class="ed-word-reference" data-word="mellon" data-original-word="mellon">mellon</a>!</p>';

        $parser = new MarkdownParser;
        $actual = $parser->text($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function test_reference_interception()
    {
        $markdown = 'mae govannen [mellon](https://www.google.com)!';
        $expected = '<p>mae govannen <a href="https://www.elfdict.com" class="ed-word-external-reference">mellon</a>!</p>';

        $parser = new MarkdownParser(new class implements IExternalToInternalUrlResolver
        {
            public function getInternalUrl(string $url): ?string
            {
                return 'https://www.elfdict.com';
            }

            public function isHostQualified(string $host): bool
            {
                return true;
            }

            public function getSources(): array
            {
                return [];
            }
        });
        $actual = $parser->text($markdown);
        $this->assertEquals($expected, $actual);
    }
}
