<?php
  namespace controllers;
  
  class ProfileController extends SecureController {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Profile', $engine);
    }
    
    public function load() {
      $model = $this->getModel();
      if ($model->hasLoadedAuthenticatedAuthor()) {
        parent::load();
      }
      
      if ($model !== null) {
        $engine = $this->_engine;
        $engine->assign('loggedIn',      $model->getLoggedIn());
        $engine->assign('myProfile',     $model->hasLoadedAuthenticatedAuthor());
        $engine->assign('author',        $model->getAuthor());
        $engine->assign('profileHtml',   \utils\StringWizard::createLinks($model->getAuthor()->profile));

        if (isset($_GET['message'])) {
          $message = preg_replace('[^a-zA-Z\\-]', '', $_GET['message']);
        } else {
          $message = '';
        }

        $engine->assign('message', $message);
      }
    }
  }
  
