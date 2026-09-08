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

    public function test_explicit_uri_label_is_shortened_to_the_destination_host()
    {
        // The label is a link of its own, and must never be allowed to misrepresent where the
        // link actually goes.
        $uri = 'https://www.evil.com/a/path/to/a/resource?a=b';
        $expected = '<p><a href="'.htmlentities($uri).'" title="Goes to: '.htmlentities($uri).'">www.evil.com</a></p>';

        $parser = new MarkdownParser;
        $actual = $parser->text('[https://www.trustworthy.com/safe]('.$uri.')');

        $this->assertEquals($expected, $actual);
    }

    public function test_uri_with_a_formatted_label_keeps_its_formatting()
    {
        $uri = 'https://www.reallylonglink.com/a/path/to/a/resource?a=b';
        $expected = '<p><a href="'.htmlentities($uri).'"><strong>bold label</strong></a></p>';

        $parser = new MarkdownParser;
        $actual = $parser->text('[**bold label**]('.$uri.')');

        $this->assertEquals($expected, $actual);
    }

    public function test_uri_with_an_image_label_keeps_its_image()
    {
        $uri = 'https://www.reallylonglink.com/a/path/to/a/resource?a=b';
        $expected = '<p><a href="'.htmlentities($uri).'"><img src="https://www.reallylonglink.com/i.png" alt="An image" /></a></p>';

        $parser = new MarkdownParser;
        $actual = $parser->text('[![An image](https://www.reallylonglink.com/i.png)]('.$uri.')');

        $this->assertEquals($expected, $actual);
    }

    public function test_uri_label_is_not_parsed_as_markdown_once_shortened()
    {
        $uri = 'https://example.com/a/path_with_underscores?a=b';
        $expected = '<p><a href="'.htmlentities($uri).'" title="Goes to: '.htmlentities($uri).'">example.com</a></p>';

        $parser = new MarkdownParser;
        $actual = $parser->text('['.$uri.']('.$uri.')');

        $this->assertEquals($expected, $actual);
    }

    public function test_short_uri_is_shortened_to_its_host()
    {
        $uri = 'https://example.com';
        $expected = '<p><a href="'.$uri.'" title="Goes to: '.$uri.'">example.com</a></p>';

        $parser = new MarkdownParser;

        $this->assertEquals($expected, $parser->text($uri));
        $this->assertEquals($expected, $parser->text('['.$uri.']('.$uri.')'));
    }

    public function test_uri_without_a_valid_host_is_left_alone()
    {
        // Underscores are not valid in a host name, so the URI fails validation and is untouched.
        $uri = 'https://my_site_name.example.com/a/path/to/a/resource?a=b';
        $expected = '<p><a href="'.htmlentities($uri).'">'.htmlentities($uri).'</a></p>';

        $parser = new MarkdownParser;
        $actual = $parser->text('['.$uri.']('.$uri.')');

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
