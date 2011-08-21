<?php
  class PageContributorsController extends Controller {
    public function __construct(TemplateEngine &$base) {
      parent::__construct('contributors');
      $base->assign('authors', $this->_model->getAuthors());
      $base->assign('activeAuthors', $this->_model->getActiveAuthors());
    }
  }
?>