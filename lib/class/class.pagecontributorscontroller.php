<?php
  class PageContributorsController extends Controller {
    public function __construct(TemplateEngine &$base) {
      parent::__construct('contributors');
      
      $model = $this->getModel();
      $base->assign('authors', $model->getAuthors());
      $base->assign('activeAuthors', $model->getActiveAuthors());
    }
  }
?>