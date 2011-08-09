<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageIndexController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('index');
      
      if ($this->_model !== null) {
        $engine->assign('languages', $this->_model->getLanguages());
      }
    }
  }
?>