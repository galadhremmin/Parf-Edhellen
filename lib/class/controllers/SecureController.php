<?php
  namespace controllers;

  class SecureController extends Controller {
    public function load() {
      $account = \auth\Session::getAccount();
      
      if ($account === null || !($account instanceof \data\entities\Account)) {
        header('Location: authenticate.page');
        return;
      }

      if ($this->requiresConfiguredAccount() && !$account->configured) {
        header('Location: authenticateComplete.page');
        return;
      }
    }
    
    protected function requiresConfiguredAccount() {
      return true;
    }
  }
  
