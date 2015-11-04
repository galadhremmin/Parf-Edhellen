<?php
  namespace controllers;
  
  class IndexController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Index', $engine);
    }
    
    public function load() {
      $model = $this->getModel();
      if ($model !== null) {
        $this->_engine->assign('reviews', $model->getReviews());
        $this->_engine->assign('sentence', $model->getSentence());
        $this->_engine->assign('loggedIn', $model->getIsLoggedIn());
      }
    }
  }
