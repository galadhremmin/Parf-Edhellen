<?php
  namespace controllers;
  
  class IndexController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Index', $engine);
    }
    
    public function load() {
      $model = $this->getModel();
      if ($model !== null) {
        $this->_engine->assign('languages', $model->getLanguages());
      }
    }
  }
