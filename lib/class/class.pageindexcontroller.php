<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageIndexController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('index', $engine);
    }
    
    public function load() {
      $model = $this->getModel();
      if ($model !== null) {
        $this->_engine->assign('languages', $model->getLanguages());
      }
    }
  }
?>
