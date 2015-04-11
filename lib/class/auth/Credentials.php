<?php
  namespace auth;
  
  session_start();
  
  class Credentials {
    const SESSION_VARS_KEY = '_edc';
    private static $_currentCredentials = null;
    private $_account;

    /**
     * Retrieves the current credentials.
     * @return \auth\Credentials
     */
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
    
    /**
     * Attempts to retrieve the credentials for the specified token, and assign it to the session container.
     * If $create is set to true, a new account will be created for the specified token, if none exist. 
     * @param string $token
     * @param boolean $create
     * @return \auth\Credentials
     */
    public static function &load($token, $create = false) {
      if ($create) {
        // Preserve compatibility with existing accounts by using legacy hashing.
        $token = Hashing::legacyHash($token);
      }
      
      $credentials = new Credentials($token, $create);
      self::$_currentCredentials = $credentials;

      return $credentials;
    }
    
    /**
     * Requires that the credentials are authorized to the specified access request.
     * Returns the credentials if the credentials are authorized, and throws an exception if they're not.
     * @param IAccessRequest $access
     * @throws \exceptions\InadequatePermissionsException
     * @return \auth\Credentials
     */
    public static function &request(IAccessRequest $access) {
      // Pass the current credentials to the access request.
      if (! self::permitted($access)) {
        throw new \exceptions\InadequatePermissionsException(get_class($access));
      }
      
      return self::current();
    }
    
    /**
     * Determines if the credentials are authorized to the specified access request.
     * Returns true if the credentials are authorized, and false if they're not.
     * @param IAccessRequest $access
     * @return boolean
     */
    public static function permitted(IAccessRequest $access) {
      // Pass the current credentials to the access request.
      $credentials =& self::current();
      return $access->request($credentials);
    }

    /**
     * Retrieves the last authenticated token from the session.
     * @return string
     */
    private static function getToken() {
      return isset($_SESSION[self::SESSION_VARS_KEY]) 
        ? (string) $_SESSION[self::SESSION_VARS_KEY] 
        : null;
    }
    
    /**
     * Assigns the specified token to the session.
     * @param string $token
     */
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
    
    /**
     * Returns an array of user groups associated with the account in possession of the credentials.
     * @return array
     */
    public function &groups() {
      return $this->_account->groups;
    }
    
    /**
     * Returns the account associated with the credentials.
     * @return \data\entities\Account
     */
    public function &account() {
      return $this->_account;
    } 
  }

