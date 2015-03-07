<?php

  namespace auth;
  
  class Hashing {
    public static function legacyHash($value) {
      if (defined('SYS_SEC_SALT')) {
        $value .= SYS_SEC_SALT;
      }
      
      if (defined('SYS_SEC_LOOP') && SYS_SEC_LOOP > 0) {
        for ($i = 0; $i < SYS_SEC_LOOP; ++$i) {
          $value = sha1($value);
        }
      }
      
      return $value;
    }
  }
