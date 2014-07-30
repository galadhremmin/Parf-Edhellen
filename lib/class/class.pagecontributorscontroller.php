<?php
  class PageContributorsController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('contributors', $engine);
    }
    
    public function load() {
      $model = $this->getModel();
      $this->_engine->assign('authors', $model->getAuthors());
      $this->_engine->assign('activeAuthors', $model->getActiveAuthors());
    }
  }
?>
