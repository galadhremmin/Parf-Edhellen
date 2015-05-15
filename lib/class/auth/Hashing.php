<?php

  namespace auth;
  
  class Hashing {
    public static function hash($data) {
      if (! function_exists('password_hash')) {
        throw new \exceptions\NotImplementedException('password_hash');
      }
      
      return password_hash($data, PASSWORD_DEFAULT, array('cost' => SYS_SEC_COST));
    }
    
    public static function needsRehash($hash) {
      return password_needs_rehash($hash, PASSWORD_DEFAULT, array('cost' => SYS_SEC_COST));
    }
    
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
