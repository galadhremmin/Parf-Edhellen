<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageIndexController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('index');
      
      $model = $this->getModel();
      if ($model !== null) {
        $engine->assign('languages', $model->getLanguages());
      }
    }
  }
?>