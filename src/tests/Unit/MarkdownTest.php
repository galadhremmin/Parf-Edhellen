<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\MarkdownParser;

class MarkdownTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testLongUriImplicitShortening()
    {
        $uri = 'https://www.reallylonglink.com/a/path/to/a/resource?a=ridiculously+long+query+string&with+multiple+parameters=true';
        $expected = '<p><a href="'.htmlentities($uri).'" title="Goes to: '.htmlentities($uri).'">www.reallylonglink.com</a></p>';
    
        $parser = new MarkdownParser;
        $actual = $parser->parse($uri);

        $this->assertEquals($expected, $actual);
    }

    public function testLongUriExplicitShortening()
    {
        $uri = 'https://www.reallylonglink.com/a/path/to/a/resource?a=ridiculously+long+query+string&with+multiple+parameters=true';
        $expected = '<p><a href="'.htmlentities($uri).'" title="Goes to: '.htmlentities($uri).'">www.reallylonglink.com</a></p>';
    
        $parser = new MarkdownParser;
        $actual = $parser->parse('['.$uri.']('.$uri.')');

        $this->assertEquals($expected, $actual);
    }

    public function testUriWithDescrition()
    {
        $uri = 'https://www.reallylonglink.com/a/path/to/a/resource?a=ridiculously+long+query+string&with+multiple+parameters=true';
        
        $markdown = '[A text sample]('.$uri.')';
        $expected = '<p><a href="'.htmlentities($uri).'">A text sample</a></p>';
    
        $parser = new MarkdownParser;
        $actual = $parser->parse($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function testTengwarTranscription()
    {
        $markdown = 'mae govannen @sindarin:mellon@!';
        $expected = '<p>mae govannen <span class="tengwar" data-tengwar-transcribe="true" data-tengwar-mode="sindarin">mellon</span>!</p>';
    
        $parser = new MarkdownParser;
        $actual = $parser->parse($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function testReferenceWithShortLanguageName()
    {
        $markdown = 'mae govannen [[s:mellon]]!';
        $expected = '<p>mae govannen <a href="/w/mellon/s" title="Navigate to mellon." class="ed-word-reference" data-word="mellon" data-original-word="mellon" data-language-short-name="s">mellon</a>!</p>';
    
        $parser = new MarkdownParser;
        $actual = $parser->parse($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function testReference()
    {
        $markdown = 'mae govannen [[mellon]]!';
        $expected = '<p>mae govannen <a href="/w/mellon" title="Navigate to mellon." class="ed-word-reference" data-word="mellon" data-original-word="mellon">mellon</a>!</p>';
    
        $parser = new MarkdownParser;
        $actual = $parser->parse($markdown);

        $this->assertEquals($expected, $actual);
    }
}
