<?php
  namespace auth;
  
  session_start();

  class Session {  
    public static function isValid() {
      $identity = self::getUnserializedSessionValues();
      
      if ($identity === null) {
        return false;
      }
      
      // the second parameter: *lazy* means to prevent man-in-the-middle attacks
      // by checking against the remote address.
      $check = self::hash($identity[SEC_INDEX_IDENTITY].$_SERVER['REMOTE_ADDR']);
      
      return $check === $identity[SEC_INDEX_CHECKSUM];
    }
    
    public static function canWriteSelf() {
      if (!self::isValid()) {
        throw new \exceptions\ErrorException('Inadequate permissions.');
      }
    }
    
    public static function canWrite($id) {
      $account = self::getAccount();
      
      if ($account === null || ($account->id != $id && !in_array('Administrators', $account->groups))) {
        throw new \exceptions\ErrorException('Inadequate permissions.');
      }
    }
    
    public static function getAccount($salted_identity = null) {
      if ($salted_identity === null) {
        $values = self::getUnserializedSessionValues();
        
        if ($values === null) {
          return null;
        }
        
        $salted_identity = $values[SEC_INDEX_IDENTITY];
      }

      $account = new \data\entities\Account();
      $account->load($salted_identity);
      
      return $account;
    }
  
    public static function register(LightOpenID &$provider) {

      if (!$provider->validate()) {
        return false;
      }
      
      return self::internalRegister($provider->identity);
    }
    
    public static function registerService($serviceIdentifier) {
      $fakeIdentity = null;
      switch ($serviceIdentifier) {
        case 'ardalambion':
          $fakeIdentity = 'MASTER-ARDALAMBION';
          break;
        case 'parviphith':
          $fakeIdentity = 'MASTER-PARVIPHITH';
          break;
        case 'tolkiendil':
          $fakeIdentity = 'MASTER-TOLKIENDIL';
          break;
      }
      
      if ($fakeIdentity == null) {
        return false;
      }
      
      return self::internalRegister($fakeIdentity);
    }
    
    public static function unregister() {
      if (!self::isValid()) {
        return;
      }
      
      unset($_SESSION['identity']);
      session_destroy();
    }
    
    public static function completeRegistration($nickname) {
      self::canWriteSelf();
      
      $account = self::getAccount();
      
      $account->nickname = $nickname;
      $account->configured = true;
      $account->save();
    }
    
    private static function internalRegister($openIdIdentity) {
      $identity = self::hash($openIdIdentity);
      $id = self::persist($identity);
      
      if ($id < 1) {
        return false;
      }
      
      self::log($id);
      
      $_SESSION['identity'] = serialize(
        array(
          SEC_INDEX_IDENTITY => $identity, 
          SEC_INDEX_CHECKSUM => self::hash($identity.$_SERVER['REMOTE_ADDR']) 
        )
      );
      
      return true;
    }
    
    private static function hash($value) {
      // TODO: * move salt to every iteration
      //       * use SHA-256/512 instead, or bcrypt
      if (defined('SYS_SEC_SALT')) {
        $value .= SYS_SEC_SALT;
      }
      
      if (defined('SYS_SEC_LOOP') && SYS_SEC_LOOP > 0) {
        for ($i = 0; $i < SYS_SEC_LOOP; ++$i) {
          $tmp   = 
          $value = sha1($value);
        }
      }
      
      return $value;
    }
    
    private static function persist($salted_identity) {
      $account = self::getAccount($salted_identity);

      if ($account === null || !$account->validate()) {
        // no such account exists, create one
        $account = new \data\entities\Account(array(
          'identity' => $salted_identity
        ));
        $account->save();
      }
      
      return $account->id;
    }

    private static function getUnserializedSessionValues() {
      if (!isset($_SESSION['identity']))
        return null;
      
      $identity = unserialize($_SESSION['identity']);
      if (!is_array($identity)) {
        return null;
      }
      
      if (count($identity) < SEC_INDEX_INDEXES) {
        return null;
      }
      
      return $identity;
    }
    
    private static function log($id) {
      $time       = time();
      $remoteAddr = self::hash($_SERVER['REMOTE_ADDR']);
    
      $stmt = \data\Database::instance()->connection()->prepare('INSERT INTO `auth_logins` (`Date`, `IP`, `AccountID`) VALUES (?, ?, ?)');
      $stmt->bind_param('isi', $time, $remoteAddr, $id);
      $stmt->execute();
      $stmt->close();
    }
  }
  
