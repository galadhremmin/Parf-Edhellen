<?php

namespace App\Helpers;

class StringHelper
{
    private static array $_slugificationNormalizationTable = [
        '(' => '-',
        ')' => '-',
        ' ' => '_',
        ';' => '_or',
        '/' => '_or_',
    ];

    private static array $_normalizationTable = [
        'θ' => 'th',
        'ʃ' => 'sh',
        'χ' => 'ch',
        'ƀ' => 'v',
        'ǝ' => 'schwa',
        'ʒ' => 'zh',
        'ŋ' => 'ng',
        'ñ' => 'ng',
        'ë' => 'e',
    ];

    private static array $_accentsNormalizationTable = [
        'é' => 'ee',
        'ý' => 'yy',
        'ú' => 'uu',
        'í' => 'ii',
        'ó' => 'oo',
        'á' => 'aa',
    ];

    private static array $_longAccentsNormalizationTable = [
        'ê' => 'eee',
        'ŷ' => 'yyy',
        'û' => 'uuu',
        'î' => 'iii',
        'ô' => 'ooo',
        'â' => 'aaa',
    ];

    private static ?array $_reversedAccentsNormalizationTable = null;
    private static ?array $_reversedLongAccentsNormalizationTable = null;

    private function __construct()
    {
        // Disable construction
    }

    public static function htmlEntities(?string $str)
    {
        if (empty($str)) {
            return $str;
        }

        return htmlentities($str, ENT_HTML5 | ENT_SUBSTITUTE | ENT_QUOTES, 'UTF-8');
    }

    public static function toLower(?string $str)
    {
        if (empty($str)) {
            return $str;
        }

        return trim(mb_strtolower($str, 'utf-8'));
    }

    public static function clean(?string $str)
    {
        if (empty($str)) {
            return $str;
        }

        return preg_replace('/[¹²³‽†√#]/u', '', $str);
    }

    /**
     * Normalizes the specified string.
     *
     * @param  bool  $transformAccentsIntoLetters  - whether accents should be normalized according to a phonetic approximation
     * @param  bool  $retainWildcard  - retains wildcard character (*)
     * @param  bool  $longAccents  - whether long accents should be normalized
     * @return string
     */
    public static function normalize(?string $str, $transformAccentsIntoLetters = true, $retainWildcard = false, $longAccents = true)
    {
        if (empty($str)) {
            return $str;
        }

        $str = preg_replace('/[’\\{\\}\\[\\]\+=!\.%]/u', '', $str);

        if (! $retainWildcard) {
            $str = str_replace('*', '', $str);
        }

        $str = self::transliterate($str, $transformAccentsIntoLetters, $longAccents);
        return strtr($str, self::$_slugificationNormalizationTable);
    }

    /**
     * Transliterates the specified string.
     *
     * @param  string  $str  - the string to transliterate
     * @param  bool  $transformAccentsIntoLetters  - whether accents should be normalized according to a phonetic approximation
     * @param  bool  $longAccents  - whether long accents should be normalized
     * @return string
     */
    public static function transliterate(string $str, bool $transformAccentsIntoLetters = true, bool $longAccents = true)
    {
        $str = self::toLower($str);
        
        if ($transformAccentsIntoLetters) {
            $normalizationTable = array_merge(self::$_normalizationTable, self::$_accentsNormalizationTable, 
                $longAccents ? self::$_longAccentsNormalizationTable : []);
        } else {
            $normalizationTable = self::$_normalizationTable;
        }

        $str = strtr($str, $normalizationTable);
        $str = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $str);

        return trim($str);
    }

    public static function normalizeForUrl(string $str)
    {
        $str = self::normalize($str);

        // Replace white space with underscore
        $str = str_replace(' ', '_', $str);

        // Remove all non-alphabetic and non-numeric characters
        $str = preg_replace('/[^0-9a-z_]/', '', $str);

        return $str;
    }

    /**
     * Reversed normalization, attempting to convert a normalized string into its accented version.
     *
     * @param  string  $str  - the string to reverse normalize
     * @param  bool  $longAccents  - whether long accents should be reversed
     * @return string
     */
    public static function reverseNormalization(string $str, bool $longAccents = true)
    {
        $normalizationTable = null;
        if ($longAccents) {
            $reversed = &self::$_reversedLongAccentsNormalizationTable;
            $normalizationTable = array_merge(self::$_accentsNormalizationTable, self::$_longAccentsNormalizationTable);
        } else {
            $reversed = &self::$_reversedAccentsNormalizationTable;
            $normalizationTable = self::$_accentsNormalizationTable;
        }

        if ($reversed === null) {
            $reversed = [];

            foreach ($normalizationTable as $key => $value) {
                $reversed[$value] = $key;
            }
        }

        // remove underline slugs
        $str = str_replace('_', ' ', $str);
        return strtr($str, $reversed);
    }

    public static function createLink($s)
    {
        return rawurlencode($s);
    }

    public static function isOnlyNonLatinCharacters(string $str): bool
    {
        return (bool) preg_match('/^\p{L}++\z/u', $str) &&
            !preg_match('/\p{Latin}/u', $str);
    }

    public static function isOnlySymbolsOrInterpunctuation(string $str): bool
    {
        return (bool) preg_match('/^[\p{S}\p{P}\p{Z}]+$/u', $str);
    }

    /**
     * Convert base64UrlAppropriate (URL-safe base64 without padding) to standard base64 (with padding)
     * 
     * This is useful when you need to decode base64UrlAppropriate strings using PHP's base64_decode(),
     * which expects standard base64 format.
     *
     * @param string $base64UrlAppropriate Base64UrlAppropriate-encoded string (URL-safe, no padding)
     * @return string Standard base64-encoded string (with padding if needed)
     */
    public static function convertBase64UrlAppropriateToStandardBase64(string $base64UrlAppropriate): string
    {
        // Add padding if needed
        $padding = 4 - (strlen($base64UrlAppropriate) % 4);
        if ($padding !== 4) {
            $base64UrlAppropriate .= str_repeat('=', $padding);
        }
        // Convert base64UrlAppropriate characters to standard base64 characters
        return strtr($base64UrlAppropriate, '-_', '+/');
    }

    /**
     * Convert standard base64 (with padding) to base64UrlAppropriate (URL-safe base64 without padding)
     * 
     * This is useful for encoding data that needs to be URL-safe, such as WebAuthn challenges
     * or other data that will be used in URLs or JSON.
     *
     * @param string $base64 Standard base64-encoded string (may have padding)
     * @return string Base64UrlAppropriate-encoded string (URL-safe, no padding)
     */
    public static function convertBase64ToBase64UrlAppropriate(string $base64): string
    {
        // Remove padding and convert to URL-safe characters
        return rtrim(strtr($base64, '+/', '-_'), '=');
    }

    public static function prepareQuotedFulltextTerm(string $term): string
    {
        return '"'.str_replace('"', '\\"', $term).'"';
    }

    /**
     * Escape all MySQL fulltext binary unique symbols in the given string.
     * This ensures that symbols such as + - @ > < ( ) ~ * " are escaped with a backslash.
     * Useful when constructing raw fulltext queries to avoid special interpretation.
     *
     * @param string $str
     * @return string
     */
    public static function escapeFulltextUniqueSymbols(string $str): string
    {
        // List of MySQL fulltext unique symbols that should be escaped in BINARY mode
        // See: https://dev.mysql.com/doc/refman/8.0/en/fulltext-boolean.html#boolean-operator
        $uniqueSymbols = ['+', '-', '@', '>', '<', '(', ')', '~', '*', '"'];
        // Escape each symbol with a backslash
        return str_replace(
            $uniqueSymbols,
            array_map(fn($s) => '\\' . $s, $uniqueSymbols),
            $str
        );
    }
}
