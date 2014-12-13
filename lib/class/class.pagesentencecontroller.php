<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageSentenceController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('sentence', $engine);
    }
    
    public function load() {
      $model = $this->getModel();
      
      if ($model !== null) {
        $this->_engine->assign('sentences', $model->getSentences());
      }
    }
  }
?>
