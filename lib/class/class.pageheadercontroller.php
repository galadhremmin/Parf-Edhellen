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
       
      $model = $this->getModel();     
      if ($model !== null) {
        $engine->assign('menu', $model->getMenu());
        $engine->assign('languages', $model->getLanguages());
        $engine->assign('background', $model->getBackgroundFile());
        $engine->assign('backgrounds', $model->getBackgroundFiles());
        $engine->assign('viewportWidth', $model->getViewportWidth());
      }
    }
  }
?>