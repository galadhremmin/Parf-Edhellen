<?php
  namespace controllers;
  
  class DashboardController extends SecureController {
    public function __construct(\TemplateEngine $engine) {
      parent::__construct('Dashboard', $engine, false);
    }
    
    public function load() {
      parent::load();
      
      $model = $this->getModel();
      if ($model !== null) {
        $this->_engine->assign('translations', $model->getTranslations());
        $this->_engine->assign('favourites',   $model->getFavourites());
        $this->_engine->assign('reviews',      $model->getReviews());
        $this->_engine->assign('message',      $model->getMessage());
      }
    }
  }
  
