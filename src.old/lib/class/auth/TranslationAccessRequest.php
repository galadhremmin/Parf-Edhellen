<?php
  namespace auth;
  
  class TranslationAccessRequest extends BasicAccessRequest {
    private $_translationID;
    private $_requireReview;

    /**
     * Creates an access request for the specified translation. The request is granted if the account
     * in possession of the credentials is a member of the Users group. If the translation has been assigned a hard owner (EnforceOwner), the account must either be a member of the Administrators group, or own the translation.
     * @param number $translationID
     */
    public function __construct($translationID) {
      parent::__construct();
      $this->_translationID = $translationID;
      $this->_requireReview = false;
    }
    
    public function request(Credentials &$credentials) {
      // perform basic authorization to ensure the user is logged in.
      if (! parent::request($credentials) || ! is_numeric($this->_translationID)) {
        return false;
      }

      // Administrators always have access rights.
      $account =& $credentials->account();
      if ($account->isAdministrator()) {
        return true;
      }

      // Require an administrator to review the changes
      $this->_requireReview = true;
      return false;
    }

    public function requiresReview() {
      return $this->_requireReview;
    }
  }
