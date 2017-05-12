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
          $currentLocale = setlocale(LC_ALL, 0);
          
          // This is necessary for the iconv-transliteration to function properly
          // Note: this ought to be unnecessary because a unicode locale should be
          // specified as application default.
          // setlocale(LC_ALL, 'sv_SE.UTF-8');
          
          // Transcribe á > ´a, ê > ^e etc.
          $normalizedStr = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
          // Switch case to lower case and trim whitespace 
          $normalizedStr = self::toLower($normalizedStr);
          // Remove everything not alphanumeric.
          $normalizedStr = preg_replace('/[^\\-\\w\\s\\*\\(\\),\\.\\?;!]+/', '', $normalizedStr); 

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
