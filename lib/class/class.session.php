<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
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
    
    public static function getAccountID($salted_identity = null) {
      if ($salted_identity === null) {
        $values = self::getUnserializedSessionValues();
        
        if ($values === null) {
          return 0;
        }
        
        $salted_identity = $values[SEC_INDEX_IDENTITY];
      }
    
      $db = Database::instance();
      
      $query = $db->connection()->prepare(
        'SELECT `AccountID` FROM `auth_accounts` WHERE `Identity` = ?'
      );
      
      $query->bind_param('s', $salted_identity);
      $query->execute();
      $query->bind_result($id);
      
      if ($query->fetch() !== true) {
        $id = 0;
      }
      
      $query->close();
      
      return $id;
    }
  
    public static function register(LightOpenID &$provider) {

      if (!$provider->validate()) {
        return false;
      }
            
      $identity = self::hash($provider->identity);
      $id = self::persist($identity);
      
      if ($id < 1) {
        return false;
      }
      
      $_SESSION['identity'] = serialize(
        array(
          SEC_INDEX_IDENTITY => $identity, 
          SEC_INDEX_CHECKSUM => self::hash($identity.$_SERVER['REMOTE_ADDR']) 
        )
      );
      
      return true;
    }
    
    public static function unregister() {
      if (!self::isValid()) {
        return;
      }
      
      unset($_SESSION['identity']);
      session_destroy();
    }
    
    private static function hash($value) {
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
    
    private static function persist($salted_identity) {
      $id = self::getAccountID($salted_identity);
      if ($id < 1) {
        // no such account exists, create one
        $id = self::createAccount($salted_identity);
      }
      
      return $id;
    }
    
    private static function createAccount($salted_identity) {
      $db = Database::instance();
      
      $query = $db->connection()->query(
        'SELECT MAX(`AccountID`) + 1 AS `NewID` FROM `auth_accounts`'
      );
      
      $nick = 'Account ';
      while ($row = $query->fetch_object()) {
        $nick .= $row->NewID;
      }
      
      $query->close();
      
      $query = $db->connection()->prepare(
        "INSERT INTO `auth_accounts` (`Identity`, `Nickname`, `DateRegistered`, `Configured`) VALUES (?, ?, NOW(), '0')"
      );
      
      $query->bind_param('ss', $salted_identity, $nick);
      $query->execute();
      
      $id = $query->insert_id;
      
      $query->close();
      
      return $id;
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
  }
?>