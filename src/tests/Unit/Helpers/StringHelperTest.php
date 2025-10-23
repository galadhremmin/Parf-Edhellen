<?php

use PHPUnit\Framework\TestCase;
use App\Helpers\StringHelper;

class StringHelperTest extends TestCase
{
    public function testHtmlEntitiesHandlesNullAndEmpty()
    {
        $this->assertNull(StringHelper::htmlEntities(null));
        $this->assertSame('', StringHelper::htmlEntities(''));
    }

    public function testHtmlEntitiesTransformsChars()
    {
        $this->assertEquals('&lt;b&gt;Bold&lt;&sol;b&gt;', StringHelper::htmlEntities('<b>Bold</b>'));
        $this->assertEquals('Tom &amp; Jerry', StringHelper::htmlEntities('Tom & Jerry'));
        $this->assertEquals('Fran&ccedil;ais', StringHelper::htmlEntities('Français'));
    }

    public function testNormalizeBasic()
    {
        $this->assertEquals('th', StringHelper::normalize('θ'));
        $this->assertEquals('sh', StringHelper::normalize('ʃ'));
        $this->assertEquals('zh', StringHelper::normalize('ʒ'));
        $this->assertEquals('ch', StringHelper::normalize('χ'));
        $this->assertEquals('ng', StringHelper::normalize('ŋ'));
        $this->assertEquals('v', StringHelper::normalize('ƀ'));
        $this->assertEquals('ng', StringHelper::normalize('ñ'));
        $this->assertEquals('-a-', StringHelper::normalize('(a)'));
    }

    public function testNormalizeAccents()
    {
        $this->assertEquals('ee', StringHelper::normalize('é'));
        $this->assertEquals('aaa', StringHelper::normalize('â'));
        $this->assertEquals('yyy', StringHelper::normalize('ŷ'));
        $this->assertEquals('uuu', StringHelper::normalize('û'));
    }

    public function testNormalizeRetainsWildcardIfRequested()
    {
        // With retainWildcard = false, * is gone
        $this->assertEquals('ab', StringHelper::normalize('a*b', false, false));
        // With retainWildcard = true, * is retained
        $this->assertEquals('a*b', StringHelper::normalize('a*b', false, true));
    }

    public function testNormalizeAccentsMatter()
    {
        // If accentsMatter is true, accents are replaced
        $this->assertEquals('ee', StringHelper::normalize('é', true));
        // If accentsMatter is false, accents are not replaced (only basic normalization)
        $this->assertEquals('e', StringHelper::normalize('é', false));
    }

    public function testNormalizeRemovesUnderscoreSlugIfConfigured()
    {
        // By default, underscores for spaces
        $this->assertEquals('a_b', StringHelper::normalize('a b'));
    }

    public function testNormalizeForUrl()
    {
        $this->assertEquals('hello_world', StringHelper::normalizeForUrl('Hello World!'));
        $this->assertEquals('cafee', StringHelper::normalizeForUrl('Café!'));
        $this->assertEquals('bonjour_123', StringHelper::normalizeForUrl('Bonjour 123'));
        $this->assertEquals('a_test_case', StringHelper::normalizeForUrl('A Test Case!'));
    }

    public function testReverseNormalizationReturnsOriginalChar()
    {
        // Should convert normalized form back to accented form if possible
        $normalized = StringHelper::normalize('é');
        $this->assertEquals('é', StringHelper::reverseNormalization($normalized));

        // If cannot convert, stays same
        $this->assertEquals('zzz', StringHelper::reverseNormalization('zzz'));

        // Should change underscore to space
        $this->assertEquals('a b', StringHelper::reverseNormalization('a_b'));
    }

    public function testCreateLinkProducesUrlEncoded()
    {
        $this->assertEquals('hello%20world', StringHelper::createLink('hello world'));
        $this->assertEquals('Fran%C3%A7ais_%25', StringHelper::createLink('Français_%'));
    }

    public function testIsOnlyNonLatinCharacters()
    {
        // Only Cyrillic
        $this->assertTrue(StringHelper::isOnlyNonLatinCharacters('тест'));
        // Only Han
        $this->assertTrue(StringHelper::isOnlyNonLatinCharacters('漢字'));
        // Mixed non-Latin scripts
        $this->assertTrue(StringHelper::isOnlyNonLatinCharacters('漢字тест'));
        // Contains at least one Latin
        $this->assertFalse(StringHelper::isOnlyNonLatinCharacters('test漢字'));
        // Contains numbers or symbols (should fail)
        $this->assertFalse(StringHelper::isOnlyNonLatinCharacters('тест1'));
        $this->assertFalse(StringHelper::isOnlyNonLatinCharacters('漢字!'));
        // Empty string (should fail)
        $this->assertFalse(StringHelper::isOnlyNonLatinCharacters(''));
    }

    public function testIsOnlySymbolsOrInterpunctuation()
    {
        $this->assertTrue(StringHelper::isOnlySymbolsOrInterpunctuation('!'));
        $this->assertTrue(StringHelper::isOnlySymbolsOrInterpunctuation('?'));
        $this->assertTrue(StringHelper::isOnlySymbolsOrInterpunctuation('!?'));
        $this->assertTrue(StringHelper::isOnlySymbolsOrInterpunctuation('!?!'));

        $this->assertFalse(StringHelper::isOnlySymbolsOrInterpunctuation('test'));
        $this->assertFalse(StringHelper::isOnlySymbolsOrInterpunctuation('test!'));
        $this->assertFalse(StringHelper::isOnlySymbolsOrInterpunctuation('test?'));
        $this->assertFalse(StringHelper::isOnlySymbolsOrInterpunctuation('test!?'));
        $this->assertFalse(StringHelper::isOnlySymbolsOrInterpunctuation('test!?!'));

        $this->assertFalse(StringHelper::isOnlySymbolsOrInterpunctuation(''));
        $this->assertTrue(StringHelper::isOnlySymbolsOrInterpunctuation(' '));
        $this->assertTrue(StringHelper::isOnlySymbolsOrInterpunctuation('  '));
    }

    public function testReverseNormalizationLongAccents()
    {
        $this->assertEquals('é', StringHelper::reverseNormalization('ee', true));
        $this->assertEquals('â', StringHelper::reverseNormalization('aaa', true));
        $this->assertEquals('ŷ', StringHelper::reverseNormalization('yyy', true));
        $this->assertEquals('û', StringHelper::reverseNormalization('uuu', true));

        $this->assertEquals('ée', StringHelper::reverseNormalization('eee', false));
        $this->assertEquals('ýy', StringHelper::reverseNormalization('yyy', false));
        $this->assertEquals('úu', StringHelper::reverseNormalization('uuu', false));
        $this->assertEquals('íi', StringHelper::reverseNormalization('iii', false));
        $this->assertEquals('óo', StringHelper::reverseNormalization('ooo', false));
        $this->assertEquals('áa', StringHelper::reverseNormalization('aaa', false));
    }
}

