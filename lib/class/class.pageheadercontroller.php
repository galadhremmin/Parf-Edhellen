<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageHeaderController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('header');
      
      $engine->assign('documentTitle', SYS_TITLE);
      
      if (preg_match('/MSIE [0-8]+/', $_SERVER['HTTP_USER_AGENT'])) {
        $engine->assign('pageTitle', SYS_TITLE);
      } else {
        $engine->assign('pageTitle', '<span class="tengwar">q7Ee 4FjR¸5$</span>');
      }
            
      if ($this->_model !== null) {
        $engine->assign('menu', $this->_model->getMenu());
        $engine->assign('languages', $this->_model->getLanguages());
        $engine->assign('background', $this->_model->getBackgroundFile());
        $engine->assign('backgrounds', $this->_model->getBackgroundFiles());
        $engine->assign('viewportWidth', $this->_model->getViewportWidth());
      }
    }
  }
?>