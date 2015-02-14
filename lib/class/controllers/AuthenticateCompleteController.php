<?php
  namespace controllers;
  
  class AuthenticateCompleteController extends SecureController {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('AuthenticateComplete', $engine);
    }
    
    public function load() {
      parent::load();
      
      // TODO: bind account info
      $account = \auth\Session::getAccount();
    }
    
    protected function requiresConfiguredAccount() {
      return false;
    }
  }
  
