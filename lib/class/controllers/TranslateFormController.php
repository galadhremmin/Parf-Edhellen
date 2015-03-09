<?php
  namespace controllers;
  
  class TranslateFormController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('TranslateForm', $engine);
    }
    
    public function load() {      
      $this->_engine->assign('title', 'WIP');

      $model = $this->getModel();
      if ($model !== null) {
        $this->_engine->assign('inventedLanguages', $model->getLanguages());
        $this->_engine->assign('wordClasses', $model->getWordClasses());
        $this->_engine->assign('wordGenders', $model->getWordGenders());
      }
    }
  }
