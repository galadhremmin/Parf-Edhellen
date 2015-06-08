<?php
  namespace controllers;


  class ReviewController extends SecureController {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Review', $engine);
    }

    public function load() {
      $model = $this->getModel();

      if ($model !== null) {
        $this->_engine->assign('review', $model->getReview());
      }
    }
  }