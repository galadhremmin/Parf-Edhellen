<?php
  namespace auth;
  
  session_start();
  
  class Credentials {
    const SESSION_VARS_KEY = '_edc';
    private static $_currentCredentials = null;
    private $_account;

    public static function &current() {
      // Retrieve the token stored in the session.
      $token       = self::getToken();
      $credentials = self::$_currentCredentials;
      
      if ($credentials === null || $credentials->account()->identity !== $token) {
        // Use the information to create an instance of the credentials class.
        $credentials = self::load($token);
      }
      
      return $credentials;
    }
    
    public static function &load($token, $create = false) {
      if ($create) {
        // Preserve compatibility with existing accounts by using legacy hashing.
        $token = Hashing::legacyHash($token);
      }
      
      $credentials = new Credentials($token, $create);
      self::$_currentCredentials = $credentials;

      return $credentials;
    }
    
    public static function &request(IAccessRequest $access) {
      // Pass the current credentials to the access request.
      if (! self::permitted($access)) {
        throw new \exceptions\InadequatePermissionsException(get_class($access));
      }
      
      return self::current();
    }
    
    public static function permitted(IAccessRequest $access) {
      // Pass the current credentials to the access request.
      $credentials =& self::current();
      return $access->request($credentials);
    }

    private static function getToken() {
      return isset($_SESSION[self::SESSION_VARS_KEY]) 
        ? (string) $_SESSION[self::SESSION_VARS_KEY] 
        : null;
    }
    
    private static function setToken($token) {
      $previousToken = self::getToken();
      $_SESSION[self::SESSION_VARS_KEY] = $token;
    }
    
    protected function __construct($token, $create = false) {
      $account = new \data\entities\Account();
      
      // Partially populate the account with data from the database.
      if (null !== $token) {        
        // Attempt to load the account associated with the token.
        $account->load($token);
        
        // If the account fails validation after being loaded, it probably doesn't
        // exist for the specified token. Create a new account in that case.
        if ($create && ! $account->validate()) {
          $account->identity = $token;
          $account->save();
        }
        
        // Record the login attempt.
        $previousToken = self::getToken();
        if ($previousToken !== $token) {
          $time       = time();
          $remoteAddr = Hashing::legacyHash($_SERVER['REMOTE_ADDR']);
        
          $stmt = \data\Database::instance()->connection()->prepare(
            'INSERT INTO `auth_logins` (`Date`, `IP`, `AccountID`) VALUES (?, ?, ?)'
          );
          $stmt->bind_param('isi', $time, $remoteAddr, $account->id);
          $stmt->execute();
          $stmt->close();
        }
        
        self::setToken($token);
      }
      
      $this->_account = $account;
    }
    
    public function &groups() {
      return $this->_account->groups;
    }
    
    public function &account() {
      return $this->_account;
    } 
  }

