<?php
  namespace auth;
  
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  
  class Credentials {
    const SESSION_VARS_KEY = '_edc';
    private static $_currentCredentials = null;
    private $_account;

    /**
     * Retrieves the current credentials.
     * @return \auth\Credentials
     */
    public static function &current() {
      if (self::$_currentCredentials == null) {
        // Use the information to create an instance of the credentials class.
        $token = self::getToken();
        self::load($token);
      }
      
      return self::$_currentCredentials;
    }
    
    /**
     * Attempts to retrieve the credentials for the specified token, and assign it to the session container.
     * If $create is set to true, a new account will be created for the specified token, if none exist. 
     * @param string $token
     * @param boolean $create
     * @return \auth\Credentials
     */
    public static function &load($token) {      
      $credentials = new Credentials($token);
      self::$_currentCredentials = $credentials;

      if ($credentials->account()->validate()) {
        self::setToken($token);
      }
      
      return $credentials;
    }
    
    /**
     * 
     * @param integer $providerID
     * @param string $email
     * @param AccessToken $token
     */
    public static function authenticate($providerID, $email, $token, $nickname = null) {
      $account = \data\entities\Account::getAccountForProviderAndEmail($providerID, $email);
      if ($account !== null) {
        $tmpToken = AccessToken::fromHash($account->identity);
        
        if ($tmpToken->shouldRehash() || ! $tmpToken->matches($token)) {
          $account->updateToken($token);
        }
        
        return self::load($tmpToken);
      }

      $account = new \data\entities\Account(array(
          'email'      => $email,
          'identity'   => $token->hashedToken,
          'providerID' => $token->providerID,
          'nickname'   => $nickname
      ));
      
      $account->save();
      
      if (! $account->validate()) {
        throw new \exceptions\ValidationException(__CLASS__);
      }
      
      return self::load($token);
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
     * @return AccessToken
     */
    private static function getToken() {
      return isset($_SESSION[self::SESSION_VARS_KEY]) 
        ? AccessToken::fromHash($_SESSION[self::SESSION_VARS_KEY]) 
        : null;
    }
    
    /**
     * Assigns the specified token to the session.
     * @param string $token
     */
    private static function setToken($token) {
      if (! ($token instanceof AccessToken)) {
        throw new \exceptions\InvalidParameterException('token');
      }
      
      $_SESSION[self::SESSION_VARS_KEY] = $token->hashedToken;
    }
    
    protected function __construct($token) {
      $account = new \data\entities\Account();
      
      // Partially populate the account with data from the database.
      if ($token instanceof AccessToken) {
        // Attempt to load the account associated with the token.
        $account->load($token);
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

