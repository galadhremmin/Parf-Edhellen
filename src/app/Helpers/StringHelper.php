<?php
namespace App\Helpers;

class StringHelper 
{
    private static $_normalizationTable = [
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
        ' ' => '_'
    ];

    private static $_accentsNormalizationTable = [
        'é' => 'ee',
        'ê' => 'eee',
        'ý' => 'yy',
        'ŷ' => 'yyy',
        'ú' => 'uu',
        'û' => 'uuu',
        'í' => 'ii',
        'î' => 'iii',
        'ó' => 'oo',
        'ô' => 'ooo',
        'á' => 'aa',
        'â' => 'aaa',
    ];

    private function __construct() 
    {
        // Disable construction
    }

    public static function htmlEntities(string $str)
    {
        return htmlentities($str, ENT_HTML5 | ENT_SUBSTITUTE | ENT_QUOTES, 'UTF-8');
    }

    public static function toLower(string $str) 
    {
        return trim(mb_strtolower($str, 'utf-8'));
    }

    public static function normalize(string $str, $accentsMatter = true, $retainWildcard = false) 
    {          
        $str = self::toLower($str);
        $str = preg_replace('/[¹²³’‽†#\\{\\}\\[\\]]/u', '', $str);

        if (! $retainWildcard) {
            $str = str_replace('*', '', $str);
        }

        if ($accentsMatter) {
            $normalizationTable = array_merge(self::$_normalizationTable, self::$_accentsNormalizationTable);
        } else {
            $normalizationTable = self::$_normalizationTable;
        }

        $str = strtr($str, $normalizationTable);

        // Do not switch locale, as the appropriate locale should be configured
        // in application configuration.
        //
        // $currentLocale = setlocale(LC_ALL, 0);
        // This is necessary for the iconv-transliteration to function properly
        // Note: this ought to be unnecessary because a unicode locale should be
        // specified as application default.
        // setlocale(LC_ALL, 'en_UK.UTF-8');

        // Transcribe á, ê, é etc.
        $str = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);

        // Mac OS X Server requires some extra 'love' because it uses a different version of iconv
        // than the rest. It transcribes é -> 'e, ê -> ^e, ë -> "e etc.
        $str = preg_replace('/[\'^"]/', '', $str);

        // restore the locale
        // setlocale(LC_ALL, $currentLocale);

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

    public static function createLink($s) 
    {
        return rawurlencode($s);
    }
}
