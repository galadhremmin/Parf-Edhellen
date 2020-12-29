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
}
