<?php
  namespace controllers;
  
  class HeaderController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Header', $engine, false);
    }
    
    public function load() {
      $this->_engine->assign('documentTitle', SYS_TITLE);
      
      if (preg_match('/MSIE [0-8]+/', $_SERVER['HTTP_USER_AGENT'])) {
        $this->_engine->assign('pageTitle', SYS_TITLE);
      } else {
        $this->_engine->assign('pageTitle', '<span class="tengwar">q7Ee 4FjR¸5$</span>');
      }
       
      $model = $this->getModel();     
      if ($model !== null) {
        $this->_engine->assign('menu', $model->getMenu());
        $this->_engine->assign('languages', $model->getLanguages());
        
        $additions = $model->getAdditions();
        $this->_engine->assign('additions', $additions);
      }
    }
  }
