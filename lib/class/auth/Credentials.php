<?php
  namespace auth;
  
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  
  class Credentials {
    const SESSION_VARS_KEY = '_ed_c';
    private static $_currentCredentials = null;
    private $_account;
    private $_copy;

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
     * @param string $token
     * @return Credentials
     * @throws \exceptions\InvalidParameterException
     */
    public static function &load($token) {      
      $credentials = new Credentials($token);
      self::$_currentCredentials = $credentials;

      if ($credentials->account()->validate()) {
        self::setToken($token);
      }
      
      return $credentials;
    }

    public static function copyFor($accountID) {
      $account = \data\entities\Account::getAccountForID($accountID);
      if ($account === null) {
        return;
      }

      $cred = new Credentials($account);
      return $cred;
    }

    /**
     * Authenticates the e-mail address with the provider by saving it to the accounts table.
     * @param integer $providerID
     * @param string $email
     * @param AccessToken $token
     * @param null $nickname
     * @return Credentials
     * @throws \exceptions\ValidationException
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
     * @throws \exceptions\InvalidParameterException
     */
    private static function setToken($token) {
      if (! ($token instanceof AccessToken)) {
        throw new \exceptions\InvalidParameterException('token');
      }
      
      $_SESSION[self::SESSION_VARS_KEY] = $token->hashedToken;
    }
    
    protected function __construct($data) {
      $account = new \data\entities\Account();
      
      // Partially populate the account with data from the database.
      if ($data instanceof AccessToken) {
        // Attempt to load the account associated with the token.
        $account->load($data);
      } else if ($data instanceof \data\entities\Account) {
        $account = $data;
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

