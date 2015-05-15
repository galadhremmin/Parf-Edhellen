<?php
  namespace controllers;
  
  class AuthenticateController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Authenticate', $engine, false);
    }
    
    public function load() {    
      $error = null;
      $model = $this->getModel();
      
      if ($model !== null) {
        $this->_engine->assign('providers', $model->getProviders());
      }
      
      $this->_engine->assign('errorMessage', $error);
    }
  }
