<?php
  namespace controllers;

  class SecureController extends Controller {
    protected $_account;
  
    public function load() {
      $credentials =& \auth\Credentials::request(new \auth\BasicAccessRequest());
      $this->_account =& $credentials->account();
      
      if ($this->_account === null || !($this->_account instanceof \data\entities\Account)) {
        header('Location: authenticate.page');
        return;
      }

      if ($this->requiresConfiguredAccount() && !$this->_account->configured) {
        header('Location: authenticate-complete.page');
        return;
      }
    }
    
    protected function requiresConfiguredAccount() {
      return true;
    }
  }

