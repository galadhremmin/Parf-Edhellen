<?php
  class PageNewsController extends Controller {
    public function __construct(TemplateEngine &$base) {
      parent::__construct('news', false);
      
      $model = parent::getModel();
      if ($model != null) {
        $base->assign('activityList', $model->getActivityList());
      }
    }
  }
?>
