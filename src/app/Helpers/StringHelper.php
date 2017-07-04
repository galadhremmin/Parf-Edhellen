<?php
  namespace App\Helpers;
  
  class StringHelper 
  {
      private function __construct() 
      {
          // Disable construction
      }
    
      public static function preventXSS(string $str, $encoding = 'UTF-8') 
      {
          return htmlspecialchars($str, ENT_QUOTES | ENT_HTML401, $encoding);
      }
      
      public static function toLower(string $str) 
      {
          return trim(mb_strtolower($str, 'utf-8'));
      }
      
      public static function normalize(string $str) 
      {          
          $str = self::toLower($str);
          $str = preg_replace('/[¹²³’\\†#*\\{\\}\\[\\]]|\\s*\\([^\\)]+\\)/u', '', $str);
          $str = strtr($str, [
              'ë' => 'e',
              'θ' => 'th',
              'ʃ' => 'sh',
              'χ' => 'ch',
              'ƀ' => 'v',
              'ǝ' => 'schwa',
              'ʒ' => 'zh',
              'ŋ' => 'ng',
              'ñ' => 'ng',
              '‽' => '?'
          ]);

          // Do not switch locale, as the appropriate locale should be configured
          // in application configuration.
          //
          // $currentLocale = setlocale(LC_ALL, 0);
          // This is necessary for the iconv-transliteration to function properly
          // Note: this ought to be unnecessary because a unicode locale should be
          // specified as application default.
          // setlocale(LC_ALL, 'sv_SE.UTF-8');

          // Transcribe á > ´a, ê > ^e etc.
          $str = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
          $numberOfCharacters = strlen($str);

          $normalizedStr = '';
          $repeat = 0;
          for ($i = 0; $i < $numberOfCharacters; $i += 1) {
              $c = $str[$i];

              switch ($c) {
                  case '^':
                      $repeat = 3;
                      break;
                  case "'":
                      $repeat = 2;
                      break;
                  default:
                      if ($repeat > 0) {
                          $c = str_pad($c, $repeat, $c);
                          $repeat = 0;
                      }

                      $normalizedStr .= $c;
              }
          }
          
          // restore the locale
          // setlocale(LC_ALL, $currentLocale);
          
          return $normalizedStr;
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
