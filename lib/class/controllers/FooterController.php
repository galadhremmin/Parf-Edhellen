<?php
  namespace controllers;
  
  class FooterController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Footer', $engine);
    }
    
    public function load() {
      $model = $this->getModel();     
      if ($model !== null) {
        $additions = $model->getAdditions();
        $this->_engine->assign('additions', $additions);
      }
    }
  }
