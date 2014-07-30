<?php
  class PageNewsController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('news', $engine, false);
    }
    
    public function load() {
      $model = parent::getModel();
      if ($model != null) {
        $this->_engine->assign('activityList', $model->getActivityList());
      }
    }
  }
?>
