<?php
  namespace controllers;

  class NewsController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('News', $engine, false);
    }
    
    public function load() {
      $model = parent::getModel();
      if ($model != null) {
        $this->_engine->assign('activityList', $model->getActivityList());
      }
    }
  }
  
