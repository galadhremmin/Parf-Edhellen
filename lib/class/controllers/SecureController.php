<?php
  namespace controllers;

  class SecureController extends Controller {
    protected $_account;
  
    public function load() {
      $this->_account = \auth\Session::getAccount();
      
      if ($this->_account === null || !($this->_account instanceof \data\entities\Account)) {
        header('Location: authenticate.page');
        return;
      }

      if ($this->requiresConfiguredAccount() && !$this->_account->configured) {
        header('Location: authenticateComplete.page');
        return;
      }
    }
    
    protected function requiresConfiguredAccount() {
      return true;
    }
  }
  
