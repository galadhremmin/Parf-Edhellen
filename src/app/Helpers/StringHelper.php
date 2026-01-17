<?php

namespace App\Helpers;

class StringHelper
{
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
        '(' => '-',
        ')' => '-',
        ' ' => '_',
        ';' => '_or',
        '/' => '_or_',
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
     * @param  bool  $accentsMatter  - whether accents should be normalized according to a phonetic approximation
     * @param  bool  $retainWildcard  - retains wildcard character (*)
     * @param  bool  $longAccents  - whether long accents should be normalized
     * @return string
     */
    public static function normalize(?string $str, $accentsMatter = true, $retainWildcard = false, $longAccents = true)
    {
        if (empty($str)) {
            return $str;
        }

        $str = self::toLower($str);
        $str = self::clean($str);
        $str = preg_replace('/[’\\{\\}\\[\\]\+=!\.%]/u', '', $str);

        if (! $retainWildcard) {
            $str = str_replace('*', '', $str);
        }

        if ($accentsMatter) {
            $normalizationTable = array_merge(self::$_normalizationTable, self::$_accentsNormalizationTable, 
                $longAccents ? self::$_longAccentsNormalizationTable : []);
        } else {
            $normalizationTable = self::$_normalizationTable;
        }

        $str = strtr($str, $normalizationTable);

        // Do not switch locale, as the appropriate locale should be configured
        // in application configuration.
        //
        $currentLocale = setlocale(LC_ALL, 0);
        // This is necessary for the iconv-transliteration to function properly
        // Note: this ought to be unnecessary because a unicode locale should be
        // specified as application default.
        setlocale(LC_ALL, 'sv_SE.UTF-8');

        // Transcribe á, ê, é etc.
        $str = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);

        // restore the locale
        setlocale(LC_ALL, $currentLocale);

        // Mac OS X Server requires some extra 'love' because it uses a different version of iconv
        // than the rest. It transcribes é -> 'e, ê -> ^e, ë -> "e etc.
        $str = preg_replace('/[\'^"\?]/', '', $str);

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

    /**
     * Escapes special characters in a string for use in MySQL FULLTEXT BOOLEAN MODE queries.
     *
     * Escapes characters that have special meaning in BOOLEAN MODE: + > < ( ) ~ " \
     * Note: The * character is NOT escaped as it's used for prefix matching.
     * Note: The - character is NOT escaped when it's part of a word (e.g., "tree-word").
     *       Only leading - (for exclusion) would need escaping, but we don't support that.
     *
     * @param string $str The string to escape
     * @return string The escaped string safe for use in MATCH() AGAINST() BOOLEAN MODE queries
     */
    public static function escapeFulltextBooleanMode(string $str): string
    {
        // Escape special characters except - (hyphen is treated as part of the word in FULLTEXT)
        // Characters to escape: + > < ( ) ~ " \
        // We exclude - because it's commonly used in normalized words (e.g., "tree-word")
        // and FULLTEXT treats it as part of the word, not a special operator (unless at start)
        return preg_replace('/([+><()~"\\\\])/', '\\\\$1', $str);
    }

    /**
     * Prepares a normalized word for use in MySQL FULLTEXT BOOLEAN MODE queries with prefix matching.
     *
     * This method:
     * 1. Checks if the term already has a wildcard (*)
     * 2. If not and the term ends with a hyphen, uses quoted phrase matching (no prefix matching)
     * 3. Otherwise, appends * for prefix matching
     * 4. Escapes special characters that have meaning in BOOLEAN MODE
     *
     * Note: FULLTEXT BOOLEAN MODE doesn't support `-*` (hyphen before asterisk), so terms ending
     * with hyphens must be searched as quoted phrases without prefix matching.
     *
     * @param string $normalizedWord The normalized word (e.g., from StringHelper::normalize())
     * @return string The prepared fulltext term ready for use in MATCH() AGAINST() BOOLEAN MODE queries
     */
    public static function prepareFulltextBooleanTerm(string $normalizedWord): string
    {
        if (strpos($normalizedWord, '*') !== false) {
            // Wildcard already present, use as-is
            $fulltextTerm = $normalizedWord;
            // Escape special characters that have meaning in BOOLEAN MODE
            $fulltextTerm = self::escapeFulltextBooleanMode($fulltextTerm);
        } elseif (strlen($normalizedWord) > 0 && $normalizedWord[strlen($normalizedWord) - 1] === '-') {
            // Term ends with hyphen - can't use `-*` syntax in FULLTEXT BOOLEAN MODE
            // Use quoted phrase to match exact term (including the trailing hyphen)
            // Inside quoted phrases, only quotes need escaping (other special chars are literal)
            $inner = str_replace('"', '\\"', $normalizedWord);
            $fulltextTerm = '"' . $inner . '"';
        } else {
            // No wildcard found, append * for prefix matching (equivalent to LIKE 'term%')
            $fulltextTerm = $normalizedWord . '*';
            // Escape special characters that have meaning in BOOLEAN MODE
            $fulltextTerm = self::escapeFulltextBooleanMode($fulltextTerm);
        }

        return $fulltextTerm;
    }
}
