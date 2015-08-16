<?php
  namespace controllers;

  class SentenceFormController extends SecureController  {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('SentenceForm', $engine);
    }

    public function load() {
      parent::load();

      $model = $this->getModel();

      if ($model !== null) {
        $this->_engine->assign('sentence',          $model->getSentence());
        $this->_engine->assign('inventedLanguages', $model->getLanguages());
      }

      $this->_engine->assign('operation', 'Add');
    }
  }