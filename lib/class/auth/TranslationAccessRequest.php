<?php
  namespace auth;
  
  class TranslationAccessRequest extends BasicAccessRequest {
    private $_translationID;
  
    /**
     * Creates an access request for the specified translation. The request is granted if the account
     * in possession of the credentials is a member of the Users group. If the translation has been assigned a hard owner (EnforceOwner), the account must either be a member of the Administrators group, or own the translation.
     * @param number $translationID
     */
    public function __construct($translationID) {
      parent::__construct();
      $this->_translationID = $translationID;
    }
    
    public function request(Credentials &$credentials) {
      // perform basic authorization to ensure the user is logged in.
      if (! parent::request($credentials) || ! is_numeric($this->_translationID)) {
        return false;
      }
      
      // New entries doesn't need to be authorized, as long as the user pass basic authorization.
      if ($this->_translationID == 0) {
        return true;
      }
      
      // Administrators always has access rights.
      $account =& $credentials->account();
      if ($account->isAdministrator()) {
        return true;
      }

      $query = null;
      $owner = 0;
      
      try {
        $db = \data\Database::instance();
        $query = $db->connection()->prepare(
          'SELECT `EnforcedOwner` FROM `translation` WHERE `TranslationID` = ?'
        );
        $query->bind_param('i', $this->_translationID);
        $query->execute();
        $query->bind_result($owner);
        $query->fetch();
        
      } catch (\Exception $ex) {
        // assume something's seriously wrong and deny access if an exception is thrown. 
        return false;
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
      
      return ($owner == 0 || $owner == $account->id);
    }
  }
