<?php
  namespace controllers;
  
  class ProfileController extends SecureController {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Profile', $engine);
    }
    
    public function load() {
      parent::load();
    
      $model = $this->getModel();
      if ($model !== null) {
        $engine = $this->_engine;
        $engine->assign('loggedIn',      $model->getLoggedIn());
        $engine->assign('myProfile',     $model->getLoadedAuthenticatedAuthor());
        $engine->assign('author',        $model->getAuthor());
        $engine->assign('profileHtml',   \utils\StringWizard::createLinks($model->getAuthor()->profile));
        $engine->assign('accountAuthor', $model->getAuthorForAccount());
      }
    }
  }
  
