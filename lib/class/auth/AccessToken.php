<?php
  namespace auth;  

  class AccessToken {
    public $token;
    public $hashedToken;
    public $providerID;
    public $identity;
    
    public static function fromToken($token) {
      $pos = strpos($token, '_');
      if ($pos === false) {
        throw new \exceptions\InvalidParameterException('token');
      }
      
      $providerID = intval(substr($token, 0, $pos));
      $identity   = substr($token, $pos + 1);
      
      return new AccessToken($providerID, $identity);
    }
    
    public static function fromHash($hash) {
      $token = new AccessToken();
      $token->hashedToken = $hash;
      
      return $token;
    }
    
    public function __construct($providerID = null, $identity = null) {
      if ($providerID === null && $identity === null) {
        $this->token       = null;
        $this->hashedToken = null;
        $this->providerID  = 0;
        $this->identity    = null;
      } else {
        if (! is_numeric($providerID)) {
          throw new \exceptions\InvalidParameterException('providerID');
        }
        
        $this->token       = $providerID.'_'.$identity;
        $this->hashedToken = Hashing::hash($this->token);
        $this->providerID  = $providerID;
        $this->identity    = $identity;
      }
    }
    
    public function matches(AccessToken &$token) {
      return password_verify($token->token, $this->hashedToken);
    }
    
    public function shouldRehash() {
      return Hashing::needsRehash($this->hashedToken);
    }
  }