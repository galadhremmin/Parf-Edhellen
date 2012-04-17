<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class ExperienceUtilities {
    public static function unescape($text) {
      if (get_magic_quotes_gpc()) {
        $text = stripslashes($text);
      }
      return $text;
    }
  }