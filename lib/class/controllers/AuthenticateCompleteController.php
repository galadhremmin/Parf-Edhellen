<?php
  namespace controllers;
  
  class AuthenticateCompleteController extends SecureController {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('AuthenticateComplete', $engine);
    }
    
    public function load() {
      parent::load();
      
      if ($this->_account->configured) {
        header('Location: profile.page?message=auth-existing');
        return;
      }
            
      if (! empty($this->_account->nickname)) {
        $this->_engine->assign('nickname', $this->_account->nickname);
      } else {
        $this->_engine->assign('nickname', '');
      }
    }
    
    protected function requiresConfiguredAccount() {
      return false;
    }
  }
  
