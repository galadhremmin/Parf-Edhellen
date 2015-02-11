<?php
  namespace controllers;
  
  class SentenceController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Sentence', $engine);
    }
    
    public function load() {
      $model = $this->getModel();
      
      if ($model !== null) {
        $this->_engine->assign('sentences', $model->getSentences());
      }
    }
  }
?>
